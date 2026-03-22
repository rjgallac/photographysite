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

### 3. Set up reCAPTCHA v3

1. Go to https://www.google.com/recaptcha/admin and create a new reCAPTCHA v3 key for your domain (e.g., `photography.ddns.net`)
2. Copy `.env.example` to `.env` in the **php** directory on your server:
   ```bash
   cd /home/rob/anotherhtml/php
   cp .env.example .env
   nano .env  # Add your actual keys here
   ```
3. Update `contact.html` with your Site Key:
   - Replace `YOUR_SITE_KEY` in the script src tag at top of page
   - Replace `YOUR_SITE_KEY` in the JavaScript constant (around line 124)
4. Set permissions on `.env`:
   ```bash
   chmod 600 .env
   ```

**Important:** The `.env` file is gitignored and should NEVER be committed to version control!

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
docker compose logs -f
```

### Restart containers:
```bash
docker compose up -d --force-recreate
```

### View form submission logs inside container:
```bash
docker exec photography-php cat /var/www/html/logs/form-submissions.log
```

### Check if PHP script is working:
```bash
curl https://photography.ddns.net/php/send-mail.php
```

### Update containers after file changes:
```bash
docker compose up -d --force-recreate
```

## Security Notes

**reCAPTCHA v3:** Protects your form from spam bots. The score threshold is set to 0.5 - adjust in `send-mail.php` if needed.

- Site keys are public and safe to commit
- Secret keys must be stored in `.env` file (gitignored)
- Never commit `.env` files or secret keys to version control
- Update reCAPTCHA keys before deploying by editing `.env` on the server
- Consider adding rate limiting for production use
- For email delivery, consider using a service like Formspree or Netlify Forms

**Debugging:** Check logs for reCAPTCHA issues:
```bash
docker exec photography-php tail -f /var/www/html/logs/form-submissions.log
```

**Troubleshooting reCAPTCHA errors:**
If you see "Server configuration error", check that `RECAPTCHA_SECRET_KEY` is set in your `.env` file.
