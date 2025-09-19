# 🚀 Render Deployment Guide

## Prerequisites

1. **GitHub Repository**: Push your code to a GitHub repository
2. **Render Account**: Sign up at [render.com](https://render.com)
3. **Database**: PostgreSQL will be automatically provisioned

## Deployment Steps

### 1. Update Repository URL

Edit `render.yaml` and update the repository URL:

```yaml
repo: https://github.com/YOUR_USERNAME/YOUR_REPO_NAME
```

Replace `YOUR_USERNAME` and `YOUR_REPO_NAME` with your actual GitHub repository details.

### 2. Deploy to Render

#### Option A: Using Render Dashboard (Recommended)

1. Go to [render.com](https://render.com) and sign in
2. Click "New +" → "Blueprint"
3. Connect your GitHub repository
4. Select the `server/render.yaml` file
5. Click "Apply" to deploy

#### Option B: Using Render CLI

```bash
# Install Render CLI
npm install -g @render/cli

# Login to Render
render login

# Deploy from render.yaml
render deploy
```

### 3. Environment Variables

The following environment variables are automatically configured in `render.yaml`:

- ✅ `APP_NAME`: "Shami Restaurant API"
- ✅ `APP_ENV`: production
- ✅ `APP_DEBUG`: false
- ✅ `APP_KEY`: Auto-generated
- ✅ `DB_CONNECTION`: pgsql
- ✅ `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Auto-configured
- ✅ `CACHE_DRIVER`: file
- ✅ `SESSION_DRIVER`: file
- ✅ `LOG_CHANNEL`: stderr

### 4. Deployment Features

#### ✅ Optimized Docker Build
- Single-stage build for faster deployment
- Reduced memory usage (256M)
- Optimized PHP extensions
- Proper caching layers

#### ✅ Database Setup
- PostgreSQL 15 database
- Automatic migrations and seeding
- Connection retry logic
- Health checks

#### ✅ CORS Configuration
- Proper CORS headers for all endpoints
- Image serving with CORS support
- Multiple endpoint options

#### ✅ Production Optimizations
- Laravel caching (config, routes, views)
- OpCache enabled
- Optimized autoloader
- Proper file permissions

### 5. Health Checks

The deployment includes health checks:

- **API Test**: `GET /api/test`
- **Menu Endpoint**: `GET /api/menu/recommendations`
- **Image Serving**: `GET /api/image/{path}`

### 6. Monitoring

After deployment, you can monitor:

- **Logs**: Available in Render dashboard
- **Metrics**: CPU, Memory, Response times
- **Health Status**: Automatic health checks

### 7. Troubleshooting

#### Build Timeout
- Build timeout is set to 30 minutes
- Deploy timeout is set to 10 minutes
- If issues persist, check Docker build logs

#### Database Connection Issues
- Database connection retry logic (30 attempts)
- Automatic migration and seeding
- Check database service status

#### CORS Issues
- All endpoints include proper CORS headers
- Test with: `curl -H "Origin: http://localhost:3000" https://your-app.onrender.com/api/test`

### 8. Post-Deployment

After successful deployment:

1. **Test API**: Visit `https://your-app.onrender.com/api/test`
2. **Test Images**: Try `https://your-app.onrender.com/api/image/dishes/your-image.jpg`
3. **Update Mobile App**: Update `mobile/lib/config/app_config.dart` with your new URL
4. **Monitor Logs**: Check Render dashboard for any issues

### 9. Custom Domain (Optional)

To use a custom domain:

1. Go to your service in Render dashboard
2. Click "Settings" → "Custom Domains"
3. Add your domain
4. Update DNS records as instructed

## Support

If you encounter issues:

1. Check Render dashboard logs
2. Verify all environment variables
3. Test endpoints manually
4. Check database connectivity

---

**Ready to deploy!** 🎉
