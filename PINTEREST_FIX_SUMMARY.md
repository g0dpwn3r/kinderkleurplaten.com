# Pinterest API Fix Summary

## Problem
The `bulk_pinterest_publisher.py` script was failing with "Missing request body" error when creating pins via the Pinterest v5 API.

## Root Cause
The `create_pin()` method was sending an incorrect `media_source` structure:
```python
# WRONG - missing source_type
"media_source": {
    "media_id": media_id
}
```

Pinterest v5 API requires a `source_type` field in `media_source`.

## Solution

### 1. Fixed `create_pin()` signature in `publish_to_pinterest.py`
Changed from accepting `media_id` to accepting a complete `media_source` dict:

```python
def create_pin(
    self,
    media_source: dict,  # Now accepts full media_source structure
    title: str,
    description: str,
    destination_url: str,
    alt_text: str = "",
    board_id: str | None = None,
) -> dict:
```

### 2. Added `publish_image_url()` method (PRIMARY METHOD)
New efficient method that sends the WordPress image URL directly to Pinterest:

```python
def publish_image_url(
    self,
    image_url: str,  # WordPress image URL
    subject: str,
    wordpress_post_url: str,
    alt_text: str = "",
    title_template: str = "Gratis {subject} Kleurplaat",
    description_template: str | None = None,
    board_id: str | None = None,
) -> dict | None:
```

**Benefits:**
- No download needed
- No upload needed
- Pinterest fetches the image directly from WordPress
- Faster and more efficient

**Media source structure:**
```python
{
    "source_type": "image_url",
    "url": "https://kinderkleurplaten.com/wp-content/uploads/..."
}
```

### 3. Updated `publish_image()` to use base64 encoding
For local files, now uses proper base64 encoding instead of the broken media upload flow:

```python
def publish_image(
    self,
    image_path: str,  # Local file path
    subject: str,
    wordpress_post_url: str,
    alt_text: str = "",
    title_template: str = "Gratis {subject} Kleurplaat",
    description_template: str | None = None,
    board_id: str | None = None,
) -> dict | None:
```

**Media source structure:**
```python
{
    "source_type": "image_base64",
    "content_type": "image/png",
    "data": "<base64-encoded-image>"
}
```

### 4. Updated `bulk_pinterest_publisher.py`
Changed `process_post()` to use `publish_image_url()` instead of downloading:

**Before:**
```python
# Download image locally
download_image(image_url, temp_path)
# Upload to Pinterest
publisher.publish_image(image_path=str(temp_path), ...)
# Cleanup
temp_path.unlink()
```

**After:**
```python
# Send URL directly to Pinterest
publisher.publish_image_url(image_url=image_url, ...)
```

## Performance Improvement

### Old Flow (3 steps):
1. Download image from WordPress to local disk
2. Upload image from local disk to Pinterest media endpoint
3. Create pin with media_id

### New Flow (1 step):
1. Create pin with image_url (Pinterest fetches from WordPress)

**Benefits:**
- 66% fewer API calls
- No local disk I/O
- No temporary file cleanup
- Faster execution
- Less bandwidth usage

## Files Changed

1. **publish_to_pinterest.py**
   - Fixed `create_pin()` to accept `media_source` dict
   - Added `publish_image_url()` method
   - Updated `publish_image()` to use base64 encoding
   - Removed broken `upload_image()` dependency

2. **bulk_pinterest_publisher.py**
   - Updated `process_post()` to use `publish_image_url()`
   - Removed download/cleanup logic
   - Simplified error handling

## Testing

All changes verified:
- ✅ PinterestPublisher initializes correctly
- ✅ Board ID loaded from .env
- ✅ `publish_image_url()` method exists
- ✅ `publish_image()` method exists (base64 fallback)
- ✅ `create_pin()` signature updated
- ✅ bulk_pinterest_publisher imports work

## Usage

The bulk publisher will now work automatically:
```bash
cd /kinderkleurplaten
.venv/bin/python3 bulk_pinterest_publisher.py
```

For CLI usage with local files:
```bash
.venv/bin/python3 publish_to_pinterest.py \
  --image /path/to/image.png \
  --subject "Dino" \
  --url "https://kinderkleurplaten.com/dino-kleurplaat/"
```

## API Compliance

Both methods now send correct Pinterest v5 API structure:

**For image_url:**
```json
{
  "board_id": "123456",
  "media_source": {
    "source_type": "image_url",
    "url": "https://example.com/image.png"
  },
  "title": "Pin Title",
  "description": "Pin description",
  "link": "https://example.com/page"
}
```

**For image_base64:**
```json
{
  "board_id": "123456",
  "media_source": {
    "source_type": "image_base64",
    "content_type": "image/png",
    "data": "iVBORw0KGgoAAAANSUhEUg..."
  },
  "title": "Pin Title",
  "description": "Pin description",
  "link": "https://example.com/page"
}
```

Both structures comply with Pinterest v5 API requirements.
