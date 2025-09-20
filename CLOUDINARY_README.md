This project supports uploading images to Cloudinary from the ManagerController.

Setup

1. Install dependencies (if you want to use the official SDK):

   composer require cloudinary/cloudinary_php guzzlehttp/guzzle

   - `guzzlehttp/guzzle` is already present in this project (check composer.lock).

2. Add one of the following to your `.env`:

   - Preferred (single variable):
     CLOUDINARY_URL=cloudinary://<API_KEY>:<API_SECRET>@<CLOUD_NAME>

   - Or the individual values:
     CLOUDINARY_CLOUD_NAME=<cloud_name>
     CLOUDINARY_API_KEY=<api_key>
     CLOUDINARY_API_SECRET=<api_secret>
     CLOUDINARY_UPLOAD_PRESET=<optional_unsigned_preset_name>

   If you provide `CLOUDINARY_UPLOAD_PRESET`, the controller will use unsigned uploads. Otherwise it will attempt signed uploads using the API key/secret.

3. (Optional) If you want to delete old images from Cloudinary when updating records you should store the `public_id` in the database as well and call the Cloudinary Admin API to destroy resources. The current implementation stores the `secure_url` in the `image_path` column.

Usage

- The ManagerController methods `createTable`, `updateTable`, `createCategory`, `updateCategory`, `createDish`, and `updateDish` now upload incoming files to Cloudinary and save the returned secure URL into the model's `image_path` column.

Testing

- Use Postman or curl to send multipart/form-data with an `image` file field to the endpoints.

Notes

- If the Cloudinary PHP SDK is installed (`cloudinary/cloudinary_php`), the controller uses it. Otherwise it falls back to a Guzzle HTTP multipart upload.
- Ensure your server's PHP temporary upload path is readable by the process and files are not larger than upload_max_filesize and post_max_size in PHP config.
