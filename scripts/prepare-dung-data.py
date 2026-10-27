"""Đọc literal của initializer, không thực thi JavaScript hoặc kết nối MongoDB."""

import argparse
import hashlib
import json
import re
import subprocess
from collections import Counter
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "database/mongodb_init.js"


class LiteralReader:
    """Chỉ chấp nhận dữ liệu literal và new Date; không có eval/exec."""

    def __init__(self, text, offset=0):
        self.text, self.pos = text, offset

    def space(self):
        while True:
            match = re.match(r"(?:\s+|//[^\n]*|/\*[\s\S]*?\*/)", self.text[self.pos:])
            if not match:
                return
            self.pos += match.end()

    def take(self, token):
        self.space()
        if not self.text.startswith(token, self.pos):
            raise ValueError(f"Cần {token!r} tại offset {self.pos}")
        self.pos += len(token)

    def string(self):
        quote = self.text[self.pos]
        self.pos += 1
        result = []
        escapes = {"n": "\n", "r": "\r", "t": "\t", "b": "\b", "f": "\f"}
        while self.pos < len(self.text):
            char = self.text[self.pos]
            self.pos += 1
            if char == quote:
                return "".join(result)
            if char == "\\":
                char = self.text[self.pos]
                self.pos += 1
                if char in "ux":
                    width = 4 if char == "u" else 2
                    result.append(chr(int(self.text[self.pos:self.pos + width], 16)))
                    self.pos += width
                else:
                    result.append(escapes.get(char, char))
            else:
                result.append(char)
        raise ValueError("Chuỗi chưa đóng")

    def value(self):
        self.space()
        if self.text[self.pos] in "\"'":
            return self.string()
        if self.text.startswith("new Date(", self.pos):
            self.take("new Date(")
            self.space()
            date = None if self.text[self.pos] == ")" else self.value()
            self.take(")")
            return {"$date": date or "runtime"}
        reference = "demoUser ? demoUser._id : null"
        if self.text.startswith(reference, self.pos):
            self.pos += len(reference)
            return {"$reference": "users.username:user_demo"}
        char = self.text[self.pos]
        if char in "[{":
            self.pos += 1
            result = [] if char == "[" else {}
            end = "]" if char == "[" else "}"
            while True:
                self.space()
                if self.text[self.pos] == end:
                    self.pos += 1
                    return result
                if char == "[":
                    result.append(self.value())
                else:
                    self.space()
                    if self.text[self.pos] in "\"'":
                        key = self.string()
                    else:
                        match = re.match(r"[\w$]+", self.text[self.pos:])
                        if not match:
                            raise ValueError(f"Khóa không hợp lệ tại {self.pos}")
                        key = match.group()
                        self.pos += match.end()
                    self.take(":")
                    if key in result:
                        raise ValueError(f"Khóa trùng: {key}")
                    result[key] = self.value()
                self.space()
                if self.text[self.pos] != end:
                    self.take(",")
        for token, value in [("true", True), ("false", False), ("null", None)]:
            if self.text.startswith(token, self.pos):
                self.pos += len(token)
                return value
        number = re.match(r"-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?", self.text[self.pos:])
        if number:
            self.pos += number.end()
            return json.loads(number.group())
        raise ValueError(f"Biểu thức ngoài tập literal cho phép tại offset {self.pos}")


def checksum(data):
    return hashlib.sha256(data).hexdigest()


def build():
    raw = SOURCE.read_bytes()
    text = raw.decode("utf-8-sig")
    collections, schemas, indexes, rows = [], {}, [], {}
    variables = {}
    for match in re.finditer(r"const (categoriesData|productsData|postsData)\s*=\s*", text):
        variables[match[1]] = LiteralReader(text, match.end()).value()
    for match in re.finditer(r"dbTarget\.createCollection\(", text):
        reader = LiteralReader(text, match.end())
        name = reader.value()
        collections.append(name)
        reader.space()
        if text[reader.pos] == ",":
            reader.take(",")
            schemas[name] = reader.value()
        rows[name] = []
    for match in re.finditer(r"dbTarget\.(\w+)\.insertMany\(", text):
        reader = LiteralReader(text, match.end())
        reader.space()
        variable = re.match(r"\w+Data", text[reader.pos:])
        rows[match[1]].extend(variables[variable.group()] if variable else reader.value())
    for match in re.finditer(r"dbTarget\.(\w+)\.createIndex\(", text):
        reader = LiteralReader(text, match.end())
        keys = reader.value()
        reader.space()
        options = {}
        if text[reader.pos] == ",":
            reader.take(",")
            options = reader.value()
        indexes.append({"collection": match[1], "keys": keys, "options": options})

    issues = []
    for collection, documents in rows.items():
        for key in ("code", "slug", "email", "username", "order_code"):
            duplicates = [value for value, count in Counter(d[key] for d in documents if key in d).items() if count > 1]
            if duplicates:
                issues.append({"collection": collection, "field": key, "duplicates": duplicates})
        required = schemas.get(collection, {}).get("validator", {}).get("$jsonSchema", {}).get("required", [])
        for index, document in enumerate(documents):
            missing = [key for key in required if key not in document]
            if missing:
                issues.append({"collection": collection, "record": index, "missing": missing})

    categories = {d["code"]: d for d in rows["categories"]}
    for product in rows["products"]:
        category = categories.get(product["category"]["code"])
        if not category or any(product["category"][k] != category[k] for k in ("code", "name", "slug")):
            issues.append({"collection": "products", "code": product["code"], "problem": "category_mismatch"})
    source_hash = checksum(raw.replace(b"\r\n", b"\n"))
    versions = []
    for ref in ("origin/main", "origin/dev", "origin/feature/auth-category-admin", "origin/feature/cart-checkout-orders"):
        result = subprocess.run(["git", "show", f"{ref}:database/mongodb_init.js"], cwd=ROOT, capture_output=True)
        versions.append({"ref": ref, "exists": result.returncode == 0, **({"bytes_git_blob": len(result.stdout), "sha256_lf": checksum(result.stdout.replace(b"\r\n", b"\n"))} if result.returncode == 0 else {})})
    dataset = {
        "source": {"path": "database/mongodb_init.js", "sha256_lf": source_hash},
        "collections": {name: [{k: v for k, v in d.items() if k not in ("_id", "created_at", "updated_at")} for d in rows[name]] for name in ("categories", "products", "posts")},
    }
    image_counts = Counter(d["image"] for d in rows["products"])
    image_codes = {}
    for product in rows["products"]:
        image_codes.setdefault(product["image"], []).append(product["code"])
    provenance_path = ROOT / "database/seeders/data/image-provenance.json"
    provenance = json.loads(provenance_path.read_text(encoding="utf-8")) if provenance_path.is_file() else {"files": {}}
    allowed_licenses = {"CC0-1.0", "CC-BY-4.0", "CC-BY-SA-4.0", "PROJECT-OWNED"}
    manifest = []
    for product in rows["products"]:
        image = product["image"]
        relative = "products/" + image
        asset_path = ROOT / "database/seeders/assets" / relative
        runtime_path = ROOT / "storage/app/public" / relative
        valid = Path(image).name == image and "/" not in image and "\\" not in image and Path(image).suffix.lower() in (".jpg", ".jpeg", ".png", ".webp", ".gif", ".jfif")
        header = asset_path.read_bytes()[:12] if asset_path.is_file() and valid else b""
        signature = header.startswith((b"\xff\xd8\xff", b"\x89PNG\r\n\x1a\n", b"GIF8")) or (header.startswith(b"RIFF") and header[8:12] == b"WEBP")
        entry = provenance.get("files", {}).get(relative, {})
        file_hash = hashlib.sha256(asset_path.read_bytes()).hexdigest() if asset_path.is_file() and valid else None
        registered = bool(file_hash and entry.get("asset_sha256") == file_hash and entry.get("expected_codes") == image_codes[image])
        role = entry.get("role") if registered else ("unverified" if header else "missing")
        pexels_illustration = (entry.get("license") == "PEXELS-LICENSE" and role == "illustration"
                               and isinstance(entry.get("source_url"), str)
                               and re.fullmatch(r"https://www\.pexels\.com/photo/[a-z0-9-]+-[0-9]+/", entry["source_url"])
                               and entry.get("license_url") == "https://www.pexels.com/license/")
        rights_ok = (entry.get("license") in allowed_licenses or pexels_illustration) and entry.get("author") and entry.get("source_url")
        if not header:
            status = "missing"
        elif not signature:
            status = "invalid"
        elif registered and role == "placeholder":
            status = "placeholder"
        elif registered and role == "illustration" and rights_ok:
            status = "illustration"
        elif registered and role == "product" and rights_ok and set(image_codes[image]).issubset(entry.get("verified_product_codes", [])):
            status = "present"
        else:
            status = "unverified"
        runtime_header = runtime_path.read_bytes()[:12] if runtime_path.is_file() and valid else b""
        runtime_signature = runtime_header.startswith((b"\xff\xd8\xff", b"\x89PNG\r\n\x1a\n", b"GIF8")) or (runtime_header.startswith(b"RIFF") and runtime_header[8:12] == b"WEBP")
        manifest.append({"code": product["code"], "name": product["name"], "image": image, "asset_path": "database/seeders/assets/" + relative, "storage_path": "storage/app/public/" + relative, "public_path": "/storage/" + relative, "valid_filename": valid, "exists": asset_path.is_file() and valid, "valid_signature": bool(signature), "runtime_exists": runtime_path.is_file() and valid, "runtime_valid_signature": bool(runtime_signature), "shared_by_products": image_counts[image], "role": role, "author": entry.get("author") if registered else None, "license": entry.get("license") if registered else None, "source_url": entry.get("source_url") if registered else None, "attribution": entry.get("attribution") if registered else None, "verified_product_codes": entry.get("verified_product_codes", []) if registered else [], "status": status})
    unique_statuses = {item["image"]: item["status"] for item in manifest}
    audit = {
        "source": {"path": "database/mongodb_init.js", "working_copy_bytes": len(raw), "sha256_raw": checksum(raw), "sha256_lf": source_hash, "versions": versions},
        "method": "Strict literal parser; no JavaScript execution; no MongoDB connection.",
        "collections": {name: {"records_in_script": len(rows[name]), "fields": sorted({k for d in rows[name] for k in d}), "required": schemas.get(name, {}).get("validator", {}).get("$jsonSchema", {}).get("required", [])} for name in collections},
        "indexes": indexes,
        "source_issues": issues,
        "hazards": {"hardcoded_database": "shop_hoatuoi", "drops_collections": collections, "drop_database": "dropDatabase(" in text, "delete_many": "deleteMany(" in text, "same_demo_password_hash": len({d["password"] for d in rows["users"]}) == 1},
        "images": {"products": len(manifest), "unique_filenames": len(image_counts), "present": sum(status == "present" for status in unique_statuses.values()), "placeholders": sum(status == "placeholder" for status in unique_statuses.values()), "illustrations": sum(status == "illustration" for status in unique_statuses.values()), "unverified": sum(status == "unverified" for status in unique_statuses.values()), "missing": sum(status == "missing" for status in unique_statuses.values()), "invalid": sum(status == "invalid" for status in unique_statuses.values()), "shared_filenames": {k: v for k, v in image_counts.items() if v > 1}},
        "import_scope": {"included": ["categories", "products", "posts"], "excluded": {"users": "Optional fresh QA users only; never reuse source hashes/contact details.", "roles": "Runtime authorization uses users.roles[]. Lead owns standalone Role migration.", "orders": "Historical sample transactions are not imported.", "carts": "Business data is never seeded.", "contacts": "Sample personal contact messages are not imported.", "wishlists": "Business data is never seeded."}},
    }
    return {
        ROOT / "database/seeders/data/flowers.json": dataset,
        ROOT / "docs/dung/source-audit.json": audit,
        ROOT / "docs/dung/product-images.json": manifest,
    }


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--write", action="store_true", help="Ghi dataset/manifest/audit; mặc định chỉ kiểm tra chúng")
    parser.add_argument("--compare", type=Path, help="Đối chiếu checksum một bản initializer khác; không thực thi file")
    args = parser.parse_args()
    outputs = build()
    mismatch = []
    for path, data in outputs.items():
        encoded = json.dumps(data, ensure_ascii=False, indent=2) + "\n"
        if args.write:
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_text(encoded, encoding="utf-8", newline="\n")
        elif not path.exists() or json.loads(path.read_text(encoding="utf-8")) != data:
            mismatch.append(str(path.relative_to(ROOT)))
    audit = outputs[ROOT / "docs/dung/source-audit.json"]
    comparison = None
    if args.compare:
        other = args.compare.read_bytes()
        comparison = {"filename": args.compare.name, "bytes": len(other), "sha256_raw": checksum(other), "sha256_lf": checksum(other.replace(b"\r\n", b"\n")), "same_after_newline_normalization": checksum(other.replace(b"\r\n", b"\n")) == audit["source"]["sha256_lf"]}
        if not comparison["same_after_newline_normalization"]:
            mismatch.append("local_copy:" + args.compare.name)
    print(json.dumps({"result": "FAIL" if mismatch else "PASS", "mode": "write" if args.write else "check", "collections": {k: v["records_in_script"] for k, v in audit["collections"].items()}, "source_issues": audit["source_issues"], "images": audit["images"], "mismatched_artifacts": mismatch, "local_copy": comparison}, ensure_ascii=False))
    return 1 if mismatch else 0


if __name__ == "__main__":
    raise SystemExit(main())
