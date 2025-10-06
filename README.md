# Ynfinite PHP Client

This PHP client package provides integration with the ynfinite platform. You'll need an active [ynfinite account](https://www.ynfinite.de/) to use this package.

## Setup Instructions

### Development Environment

Navigate to the development directory and set up Docker:

```bash
cd development/docker
sudo docker compose build
sudo docker compose up -d
sudo docker compose exec ynfinite-client composer install
```

Verify the Docker container is running correctly.

Start the hot reload development server:

```bash
nvm use 20
npm install
npm run dev:hot
```

Access your development environment at [http://localhost:3100](http://localhost:3100/)

### Available Build Commands

-   `npm run build` - Build all assets (CSS + JS)
-   `npm run build:js` - Build JavaScript only
-   `npm run build:css` - Build CSS only
-   `npm run build:watch` - Watch and build assets
-   `npm run dev` - Standard Vite dev server (port 3000)
-   `npm run dev:hot` - Custom hot reload server (port 3100)
-   `npm run dev:assets` - Asset watching with concurrent builds

### Common Issues

#### Cache Directory Permissions

If you encounter:

```
Uncaught RuntimeException: Route collector cache file directory /var/www/config/../tmp/cache is not writable
```

Fix with:

```bash
sudo docker compose exec ynfinite-client chmod 777 tmp -R
```

#### Node Version Problems

Ensure you're using Node 20:

```bash
nvm use 20
```

#### Hot Reload Issues

Verify the hot reload server is active: `npm run dev:hot`

## Migrating Vite Hot Reload to Another Project

### Required Files

Copy these files to your project:

```
vite.config.js
postcss.config.js
build.mjs
hot-reload-snippet.html
scripts/
README.md
```

### Package.json Configuration

Add these to your `package.json`:

```json
"scripts": {
		"dev": "vite",
		"build": "node build.mjs",
		"build:js": "vite build",
		"build:css": "node scripts/build-css.mjs",
		"build:css:verbose": "node scripts/build-css.mjs --verbose",
		"build:watch": "concurrently \"npm run build:css -- --watch\" \"vite build --watch\"",
		"dev:hot": "node scripts/dev-server.mjs",
		"dev:assets": "concurrently \"npm run build:css -- --watch --hot\" \"vite build --watch\"",
		"setup:hot": "node scripts/setup-hot-reload.mjs",
		"preview": "vite preview",
		"clean": "rimraf public/assets/css/* public/assets/js/*",
		"install:deps": "npm install"
	},
	"devDependencies": {
		"vite": "^5.0.0",
		"sass": "^1.69.0",
		"autoprefixer": "^10.4.0",
		"postcss": "^8.4.0",
		"postcss-preset-env": "^9.3.0",
		"postcss-nesting": "^12.0.0",
		"rimraf": "^5.0.0",
		"concurrently": "^8.2.0",
		"chokidar": "^3.5.0",
		"browser-sync": "^2.29.0",
		"ws": "^8.14.0"
	}
```

### Install

run npm install

### Vite Configuration Updates

Modify these paths in `vite.config.js`:

-   Line 4: `root: './your-assets-folder'` (probably already right)
-   Line 7: `outDir: '../../your-public-folder'` (probably already right)
-   Lines ~17: Update input paths to your JS files

Modify these paths in `build-css.mjs`:

-   Line 19: Enter Css files names that needs to compile

### Launch

Follow the setup instructions above to start development.
