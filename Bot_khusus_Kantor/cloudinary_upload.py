"""
Cloudinary Integration for Bot Migrasi
Upload images to Cloudinary cloud storage
"""
import os
import json

# Try to import cloudinary, provide instructions if not available
try:
    import cloudinary
    import cloudinary.uploader
    CLOUDINARY_AVAILABLE = True
except ImportError:
    CLOUDINARY_AVAILABLE = False

current_dir = os.path.dirname(os.path.abspath(__file__))

def load_cloudinary_config():
    """Load cloudinary config from runtime_config.json or environment"""
    config_path = os.path.join(current_dir, 'cloudinary_config.json')
    
    if os.path.exists(config_path):
        with open(config_path, 'r') as f:
            return json.load(f)
    
    # Fallback to environment variables
    return {
        'cloud_name': os.environ.get('CLOUDINARY_CLOUD_NAME', ''),
        'api_key': os.environ.get('CLOUDINARY_API_KEY', ''),
        'api_secret': os.environ.get('CLOUDINARY_API_SECRET', '')
    }

def init_cloudinary():
    """Initialize Cloudinary with config"""
    if not CLOUDINARY_AVAILABLE:
        return False
    
    config = load_cloudinary_config()
    
    if not config.get('cloud_name') or not config.get('api_key'):
        return False
    
    cloudinary.config(
        cloud_name=config['cloud_name'],
        api_key=config['api_key'],
        api_secret=config['api_secret'],
        secure=True
    )
    return True

def upload_to_cloudinary(local_path, public_id=None, folder="bot-migrasi"):
    """
    Upload image to Cloudinary
    Returns cloud URL or None if failed
    """
    if not CLOUDINARY_AVAILABLE:
        return None
    
    if not os.path.exists(local_path):
        return None
    
    try:
        # Generate public_id from filename if not provided
        if not public_id:
            public_id = os.path.splitext(os.path.basename(local_path))[0]
        
        result = cloudinary.uploader.upload(
            local_path,
            public_id=public_id,
            folder=folder,
            overwrite=True,
            resource_type="image"
        )
        
        return result.get('secure_url')
    except Exception as e:
        print(f"Cloudinary upload error: {e}")
        return None

def delete_from_cloudinary(public_id):
    """Delete image from Cloudinary"""
    if not CLOUDINARY_AVAILABLE:
        return False
    
    try:
        cloudinary.uploader.destroy(public_id)
        return True
    except:
        return False
