# SSL Certificates

This directory is for SSL certificates used in production deployments.

## ⚠️ Security Notice

**Never commit SSL certificates to this repository!** This is a public repository, and committing private keys or certificates would be a serious security vulnerability.

## Setup Instructions

### For Development (Self-signed certificates)

The Docker setup automatically generates self-signed certificates for development:

```bash
# Certificates are automatically generated when running Docker
docker-compose up -d
```

### For Production (Custom certificates)

1. **Create the SSL directory:**
   ```bash
   mkdir -p ssl
   ```

2. **Add your production certificates:**
   ```bash
   # Copy your certificate files (replace with your actual files)
   cp /path/to/your/certificate.pem ssl/cert.pem
   cp /path/to/your/private-key.pem ssl/key.pem
   
   # Set proper permissions
   chmod 644 ssl/cert.pem
   chmod 600 ssl/key.pem
   ```

3. **Run with production compose file:**
   ```bash
   docker-compose -f docker-compose.prod.yml up -d
   ```

## File Structure

```
ssl/
├── cert.pem    # SSL certificate (public)
├── key.pem     # Private key (keep secure!)
└── README.md   # This file
```

## Git Ignore Rules

The following files are ignored by Git to prevent accidental commits:

- `ssl/*.pem` - All PEM files in ssl directory
- `*.pem` - Any PEM files anywhere in the project
- `*.key` - Private key files
- `*.crt` - Certificate files

## Troubleshooting

If you see SSL-related errors:

1. **Check file permissions:** Certificates should be readable, private keys should be 600
2. **Verify file paths:** Ensure certificates are in the `ssl/` directory
3. **Check certificate validity:** Ensure your certificates are not expired
4. **Docker volumes:** Make sure the SSL directory is properly mounted in Docker

## Security Best Practices

- ✅ Use strong private keys (2048+ bits)
- ✅ Keep private keys secure and never share them
- ✅ Use proper file permissions (600 for keys, 644 for certs)
- ✅ Regularly rotate certificates
- ✅ Use Let's Encrypt or other trusted CAs for production
- ❌ Never commit certificates to version control
- ❌ Never share private keys
- ❌ Don't use self-signed certificates in production 