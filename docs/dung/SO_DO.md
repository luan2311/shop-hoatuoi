# Sơ đồ dữ liệu và luồng nghiệp vụ

Sơ đồ thể hiện hợp đồng MongoDB nguồn và phần sales/auth hiện có trên commit Tiến `89242cd`, được kiểm thử từ nhánh Dũng. Category/Post đã có dữ liệu seed nhưng model/UI tương ứng chưa chuyển xong; Role/Wishlist/Contact chưa phải module Mongo hoạt động hoàn chỉnh. Quan hệ dưới đây là liên kết logic, không phải SQL foreign key.

## Kiến trúc Laravel – MongoDB

```mermaid
flowchart LR
    Browser[Khách hàng / Admin] -->|HTTP| Routes[Laravel routes + middleware]
    Routes --> Controllers[Controllers + Form Requests]
    Controllers --> Services[SalesService]
    Services --> Models[Laravel MongoDB models]
    Models -->|CRUD / transaction| Mongo[(MongoDB replica set)]
    Controllers --> Views[Blade views]
    Views --> Browser
    Controllers --> Images[storage/app/public + public storage link]

    Init[database/mongodb_init.js] -->|parser literal, offline| Dataset[flowers.json + source checksum]
    Dataset --> Importer[FlowerDataset + SafeFlowerImporter]
    Importer --> Guard{local/testing + loopback + test/dev DB}
    Guard -->|apply| Mongo
    Guard -->|preview| Report[Counts only; no writes]
    Rights[Image provenance ledger] --> ImageAssets[Tracked licensed illustration assets]
    ImageAssets -->|install-assets| Images

    PHPUnit[QA launcher] -->|unique database per test| MongoQA[(dungQA on loopback :27028)]
```

Sales/auth được kiểm tra bằng route/model hiện có; Catalog, upload ảnh và dashboard chung chưa được vẽ như module đã tích hợp.

```mermaid
erDiagram
    USERS ||--o| CARTS : "user_id ObjectId"
    USERS ||--o{ ORDERS : "user_id ObjectId"
    USERS ||--o{ WISHLISTS : "source contract"
    CATEGORIES ||--o{ PRODUCTS : "embedded category.code"
    PRODUCTS ||--o{ WISHLISTS : "source product_code"
    PRODUCTS ||--o{ CART_ITEMS : "product_code"
    CARTS ||--o{ CART_ITEMS : "embedded items"
    ORDERS ||--|{ ORDER_ITEMS : "embedded snapshot"
    USERS {
        ObjectId _id
        string username
        string email
        string password_hash
        array roles
    }
    CATEGORIES {
        ObjectId _id
        string code
        string name
        string slug
    }
    PRODUCTS {
        ObjectId _id
        string code
        string slug
        int price_VND
        int stock
        string status
        string image
        object category
    }
    CARTS {
        ObjectId _id
        ObjectId user_id
        array items
    }
    CART_ITEMS {
        string product_code
        int quantity
    }
    ORDERS {
        ObjectId _id
        string order_code
        string checkout_key
        ObjectId user_id
        object customer
        array items
        int total_amount
        string status
    }
    ORDER_ITEMS {
        string product_code
        string product_name
        int price
        int quantity
        int subtotal
    }
    WISHLISTS {
        ObjectId user_id
        string product_code
    }
    CONTACTS {
        string code
        string full_name
        string message
    }
    POSTS {
        string code
        string slug
        string title
        date published_at
    }
    ROLES {
        string name
        array permissions
    }
```

`CART_ITEMS` và `ORDER_ITEMS` là hai kiểu document **nhúng**, không phải hai collection riêng. Tổng collection nguồn vẫn là 9. Roles collection là dữ liệu lịch sử; auth hiện tại đọc `users.roles[]`. Đơn legacy có thể có user_id string, giá Decimal128; importer mới giữ giá integer và sales có lớp đọc legacy.

## Luồng nhập dữ liệu an toàn

```mermaid
flowchart TD
    A[Initializer giữ nguyên] --> B[Parser literal offline]
    B --> C[Dataset catalog và manifest ảnh]
    C --> D[Kiểm tra checksum và dữ liệu]
    D --> E[Guard env, loopback, database riêng và target]
    E --> F[Ping và kiểm tra xung đột định danh]
    F --> G{Apply?}
    G -->|Không| H[PREVIEW, không ghi]
    G -->|Có| I[Index additive cho catalog]
    I --> J[Upsert chỉ setOnInsert]
    J --> K[Đã có: bỏ qua hoàn toàn]
    J --> L[Chưa có: thêm với ObjectId và provenance]
    K --> M[Báo inserted/skipped/updated=0]
    L --> M
```

Một đợt import không phải transaction toàn bộ. Lỗi kết nối/validator có thể để lại một phần insert hợp lệ; báo FAIL kèm progress và chạy lại an toàn sau review, không xóa dữ liệu.

## Activity – đặt hàng

```mermaid
flowchart TD
    A[Khách đăng nhập] --> B[GET checkout; tạo UUID token]
    B --> C[POST checkout]
    C --> D{Request hợp lệ và giỏ còn hàng?}
    D -->|Không| E[Trả lỗi; giữ nguyên giỏ, kho và đơn]
    D -->|Có| F[Bắt đầu transaction]
    F --> G{User đã dùng checkout token này?}
    G -->|Có| H[Trả đơn cũ; không mua lại giỏ mới]
    G -->|Chưa| I[Đọc giá và trạng thái từ MongoDB]
    I --> J{Đủ tồn kho?}
    J -->|Không| K[Rollback; giữ nguyên giỏ]
    J -->|Có| L[Trừ kho có điều kiện]
    L --> M[Tạo order snapshot]
    M --> N[Xóa items khỏi cart]
    N --> O[Commit và trả trang thành công]
    O --> P{Admin hủy đơn hợp lệ?}
    P -->|Có| Q[Transaction hoàn kho đúng một lần]
```

## Sequence – checkout

```mermaid
sequenceDiagram
    actor Buyer as Khách hàng
    participant Web as Laravel route/controller
    participant Request as OrderRequest
    participant Sales as SalesService
    participant DB as MongoDB replica set

    Buyer->>Web: GET /checkout
    Web->>DB: Đọc cart và products
    DB-->>Web: Tóm tắt giỏ
    Web-->>Buyer: Form + UUID checkout_token
    Buyer->>Web: POST /checkout (token + thông tin giao hàng)
    Web->>Request: Validate token, người nhận, thanh toán
    Request-->>Web: Dữ liệu hợp lệ
    Web->>Sales: checkout(user, validated data)
    Sales->>DB: Bắt đầu transaction, tra checkout_key theo owner
    alt Token đã xử lý
        DB-->>Sales: Order cũ
        Sales-->>Web: Trả lại order cũ, không sửa cart/kho
    else Token mới
        Sales->>DB: Đọc cart/product; kiểm tra status, giá, stock
        Sales->>DB: Trừ kho có điều kiện; tạo snapshot order; clear cart
        DB-->>Sales: Ghi thành công
        Sales->>DB: Commit transaction
        Sales-->>Web: Order mới
    end
    Web-->>Buyer: Redirect /orders/success
```

Lỗi validation trả về trước khi gọi SalesService; lỗi ghi MongoDB trong transaction trả lỗi HTTP và rollback thay đổi kho/đơn/giỏ. Các nhánh lỗi được kiểm tra bằng Feature tests; sơ đồ không khẳng định Catalog hoặc trình duyệt đã nghiệm thu.

## Luồng bán hàng đang có code

```mermaid
flowchart TD
    A[User đăng nhập] --> B[Thêm giỏ bằng product_id ObjectId]
    B --> C[Giỏ lưu code và quantity; lấy giá server]
    C --> D[GET checkout cấp UUID token]
    D --> E[POST checkout: validate và transaction]
    E --> F{Đã có checkout_key của user?}
    F -->|Có| G[Trả đơn cũ, giữ giỏ mới và kho]
    F -->|Chưa| H[Kiểm tra sản phẩm, giá, stock và status]
    H --> I[Trừ kho có điều kiện]
    I --> J[Tạo order snapshot và clear cart]
    J --> K[Pending]
    K --> L[Processing]
    L --> M[Shipped]
    M --> N[Completed]
    K --> O[Cancelled: transaction hoàn kho một lần]
    L --> O
```

Giao diện Catalog đứng trước bước thêm giỏ chưa được tích hợp. COD/Banking là lựa chọn phương thức; chưa có API thanh toán ngân hàng. Use Case nguồn: [USE_CASE.puml](USE_CASE.puml).
