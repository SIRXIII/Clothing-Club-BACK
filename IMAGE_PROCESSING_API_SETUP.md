# Image Processing API Setup Guide

## Overview
This feature allows partners to upload product images that are automatically enhanced using Fashn.ai's virtual try-on API. The processed images are stored in the database and linked to products.

## Backend Setup (Laravel)

### 1. Environment Variables
Add these variables to your `.env` file:

```env
# Third-Party Image Processing API (Fashn.ai)
# Get your API key from: https://fashn.ai
FASHN_API_KEY=your_fashn_api_key_here

# Model URLs for virtual try-on (Optional - defaults are provided)
MALE_MODEL_URL=https://v3.fal.media/files/panda/jRavCEb1D4OpZBjZKxaH7_image_2024-12-08_18-37-27%20Large.jpeg
FEMALE_MODEL_URL=https://v3.fal.media/files/panda/jRavCEb1D4OpZBjZKxaH7_image_2024-12-08_18-37-27%20Large.jpeg

# Hetzner Storage Configuration (Required for full image URLs)
HETZNER_S3_URL=https://your-bucket-name.your-region.hetzner-objects.com
# OR if not using custom domain:
# HETZNER_S3_URL will be constructed from HETZNER_S3_ENDPOINT and HETZNER_S3_BUCKET

# Example:
# HETZNER_S3_ENDPOINT=https://fsn1.your-storage.hetzner.cloud
# HETZNER_S3_BUCKET=your-bucket-name
# HETZNER_S3_KEY=your-access-key
# HETZNER_S3_SECRET=your-secret-key
# HETZNER_S3_REGION=eu-central-1
```

### 2. Get Fashn.ai API Key
1. Visit [https://fashn.ai](https://fashn.ai)
2. Sign up for an account
3. Navigate to API settings
4. Generate an API key
5. Copy the key and paste it in your `.env` file

### 3. API Endpoint
The image processing endpoint is now available at:
```
POST /api/products/process-image
```

**Headers:**
```
Authorization: Bearer {your_auth_token}
Content-Type: multipart/form-data
```

**Request Body:**
- `image` (required): Image file (max 10MB)
- `product_id` (optional): If provided, the processed image will be linked to this product
- `gender` (optional): `Male` or `Female` (default: Female)

**Response:**
```json
{
  "success": true,
  "message": "Image processed and stored successfully",
  "processed_image": {
    "url": "https://your-storage-url.com/product/processed/processed_1234567890_abc123.jpg",
    "path": "product/processed/processed_1234567890_abc123.jpg",
    "original_api_url": "https://fashn.ai/processed-image-url.jpg"
  }
}
```

### 4. How It Works

1. **Upload**: User uploads an image through the frontend
2. **Convert**: Backend converts image to base64
3. **Verify**: Backend checks API credits with Fashn.ai
4. **Process**: Image is sent to Fashn.ai for virtual try-on processing
5. **Poll**: Backend polls Fashn.ai API every 3 seconds (max 90 seconds)
6. **Download**: Once complete, processed image is downloaded
7. **Store**: Image is stored in Hetzner storage
8. **Link**: Image is linked to product in database
9. **Return**: URL is returned to frontend

### 5. Database Storage
Processed images are stored in the `product_images` table with:
- `product_id`: ID of the product
- `image_path`: Path in Hetzner storage
- `sort_order`: Display order
- `is_primary`: Whether it's the primary image

## Frontend Integration

### Upload Methods
Users can choose between two upload methods:
1. **Direct Upload**: Traditional file upload
2. **Enhanced Quality**: Upload with AI processing via Fashn.ai

### User Flow
1. Click "Enhanced Quality" button
2. Select product image
3. System shows processing indicator
4. Once complete, enhanced image is displayed with "Enhanced ✨" badge
5. On product submission, enhanced image is automatically linked

### API Call Example
```javascript
const formData = new FormData();
formData.append('image', file);
formData.append('gender', 'Female');

const response = await API.post('/products/process-image', formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
});
```

## Error Handling

### Common Errors:
1. **"Third-party API key not configured"**
   - Solution: Add `FASHN_API_KEY` to `.env`

2. **"No API credits available"**
   - Solution: Add credits to your Fashn.ai account

3. **"Image processing timed out"**
   - Solution: Image might be too large or API is slow. Try a smaller image.

4. **"Invalid API key or API service unavailable"**
   - Solution: Check your API key is correct and Fashn.ai service is running

## Testing

### Test the API endpoint:
```bash
curl -X POST http://your-api-url/api/products/process-image \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -F "image=@/path/to/test-image.jpg" \
  -F "gender=Female"
```

### Expected Processing Time:
- Average: 15-30 seconds
- Maximum: 90 seconds
- If timeout occurs, image might be too complex

## Monitoring

Check Laravel logs for processing status:
```bash
tail -f storage/logs/laravel.log
```

Look for these log entries:
- `Checking API credits...`
- `Submitting prediction job...`
- `Prediction started`
- `Processing status`
- `Downloading processed image`
- `Processed image saved to product`

## Cost Considerations

Each image processed consumes 1 credit from your Fashn.ai account. Monitor your usage:
- Credits are checked before each processing
- Failed processing doesn't consume credits
- Monitor your credit balance in Fashn.ai dashboard

## Security

- API key is stored securely in `.env` file
- Never commit `.env` to version control
- Use environment-specific API keys for different environments
- Rate limiting is handled by authentication middleware

## Support

For issues related to:
- **Fashn.ai API**: Contact [Fashn.ai Support](https://fashn.ai/support)
- **Backend Implementation**: Check Laravel logs
- **Frontend UI**: Check browser console

## Future Enhancements

Potential improvements:
- Support for multiple AI models
- Batch processing
- Background job processing for long operations
- Webhook support for async processing
- Image optimization before sending to API
- Custom model training

