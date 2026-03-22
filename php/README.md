# Photography Website - PHP & Docker Setup

## Prerequisites
- Docker and Docker Compose installed on your Raspberry Pi
- Port 80 available (or update docker-compose.yml if using a different port)

## Setup Instructions

### 1. Start the containers
```bash
cd php
docker compose up -d --build
```

This will:
- Build the PHP-FPM container with GD library support
- Start nginx serving your static files and routing .php requests to PHP-FPM

### 2. Access your site
Open http://localhost or your Raspberry Pi's IP address in a browser.

### 3. Configure email settings
Edit `send-mail.php` and update:
```php
$to = '[your-email@example.com]'; // Your actual email address
```

## File Structure
```
photographysite/
├── index.html          # Home page
├── about.html          # About page
├── blog.html           # Blog page
├── info.html           # Services page
├── contact.html        # Contact page (with AJAX form)
├── styles.css          # All styling
└── php/                # PHP backend files
    ├── send-mail.php   # Form handler
    ├── Dockerfile      # PHP-FPM image build
    ├── docker-compose.yml  # Container orchestration
    └── nginx.conf      # Nginx configuration
```

## Troubleshooting

### Check container logs:
```bash
docker compose -f php/docker-compose.yml logs -f
```

### Restart containers:
```bash
docker compose -f php/docker-compose.yml restart
```

### Recreate containers:
```bash
docker compose -f php/docker-compose.yml up -d --build
```

## Security Notes
- The nginx config denies access to .git and .env files
- Update the `$to` email in send-mail.php before deploying
- Consider adding rate limiting for production use
- For sensitive data, consider using a form service like Formspree or Netlify Forms instead of PHP mail()
