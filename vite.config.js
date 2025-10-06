import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
	root: './development/assets',
	publicDir: false,

	build: {
		outDir: '../../public/assets',
		emptyOutDir: false,
		sourcemap: true,
		lib: false, // Ensure we're building an app, not a library

		rollupOptions: {
			input: {
				// JavaScript Entry Points
				brycks_classic: resolve(process.cwd(), 'development/assets/js/brycks_classic.js'),
				brycks_modern: resolve(process.cwd(), 'development/assets/js/brycks_modern.js'),
				brycks_premium: resolve(process.cwd(), 'development/assets/js/brycks_premium.js'),
				brycks_rounded: resolve(process.cwd(), 'development/assets/js/brycks_rounded.js'),
				brycks_tiles: resolve(process.cwd(), 'development/assets/js/brycks_tiles.js'),
				themes: resolve(process.cwd(), 'development/assets/js/themes.js'),
			},

			output: {
				entryFileNames: 'js/[name].js',
				chunkFileNames: 'js/[name]-[hash].js',
				assetFileNames: (assetInfo) => {
					if (assetInfo.name && assetInfo.name.endsWith('.css')) {
						return 'css/[name][extname]'
					}
					return 'assets/[name][extname]'
				},
			},
		},

		minify: 'esbuild',
		target: 'es2015',
	},

	css: {
		preprocessorOptions: {
			scss: {
				quietDeps: true,
			},
		},
	},

	resolve: {
		alias: {
			'@': resolve(process.cwd(), 'development/assets'),
			'@scss': resolve(process.cwd(), 'development/assets/scss'),
			'@js': resolve(process.cwd(), 'development/assets/js'),
		},
	},

	server: {
		host: 'localhost',
		port: 3000,
		open: false,
	},
})
