# WordPress Site Customization – Twenty Twenty-Four Child Theme

This project extends and enhances a live WordPress site built on the Twenty Twenty-Four theme. The modifications simulate real-world maintenance, plugin debugging, theme customization, and performance optimization.

---

## 📁 Folder Structure

```
wp-content/
└── themes/
    └── twentytwentyfour-child/
        ├── functions.php
        ├── style.css
        ├── custom.js
		├── archive-projects.php
		├── js/
        │   └── project-filter.js
        ├── parts/
        │   └── header.html
        ├── templates/
        │   └── index.html
        └── README.md
```

---

## ✅ Task Summary

### 1. Customize Existing Child Theme
- **Header layout**: Updated `header.html` to align logo left and menu right using Flexbox.
- **Responsive CSS**: Font sizes adjusted for mobile devices.
- **Footer background**: Custom background color applied using `.wp-block-template-part` targeting.
- **JavaScript**: `custom.js` enqueued to log a message in the browser console.

### 2. Enhance Navigation
- **Custom `wp_nav_menu()`** used instead of block nav.
- **Custom Walker Class**: Supports multi-level dropdowns and adds `aria-haspopup`, `aria-expanded`, and `role="menuitem"`.
- **Keyboard Accessibility**: Tab, Enter, Esc support with responsive toggler.
- **Mobile Toggle**: JS-powered hamburger menu for screens <768px.

### 3. Custom Post Type Extension
- **Taxonomy**: Registered new custom taxonomy `project_type` for existing `projects` CPT.
- **AJAX Filter**: Archive page template modified to support front-end AJAX filtering by taxonomy terms.

### 4. Debug & Optimize Plugin Conflict
- **ACF Fields Fix**: Identified a conflicting plugin (e.g., page builder or security plugin).
- **Resolution**: Disabled or rewrote the conflict hook. Verified ACF fields now save properly.

### 5. Shortcode Refactoring
- **Existing Team Shortcode**:
  - Converted to use `WP_Query`.
  - Added optional attributes: `taxonomy`, `order`, `orderby`.
  - Implemented transient caching for better performance.
  - Refactored into `includes/shortcode-team.php`.

### 6. WooCommerce Checkout Improvement
- **New Field**: “How did you hear about us?” added to checkout form.
- **Saved as Order Meta** and displayed in admin.
- **GDPR Compliance**: Consent checkbox added with conditional rendering based on field input.

### 7. WP Cron Review
- **Audit**: Reviewed with `wp cron event list`.
- **New Cron Job**: Added custom task to set expired posts (by meta or date) to `draft` daily.

### 8. Add REST API Endpoint
- **Endpoint**: `/wp-json/custom/v1/latest-posts`
- **Returns**: Latest 5 posts with featured image URL, excerpt, categories.
- **Supports**: JSON response, cache headers.

### 9. Security Audit (Quick)
- **XML-RPC**: Disabled via `functions.php` and `.htaccess`.
- **File Editing**: Disabled with `define( 'DISALLOW_FILE_EDIT', true );`
- **Sanitization**: All custom inputs validated with `sanitize_text_field()` and `esc_html()`.

### 10. Optional: Gutenberg Block Customization
- **Custom Block**: Created with `block.json`, includes support for inner blocks.
- **Editor Style**: Added scoped CSS and block assets.
- **Block Features**: Reusable container with user-selected background color and layout control.

---

## ⚙️ Installation

1. Upload `twentytwentyfour-child` to `/wp-content/themes/`
2. Activate via **Appearance → Themes**
3. Go to **Appearance → Menus** to assign and structure the navigation
4. Visit **Appearance → Editor** to check template parts
5. Run WP-CLI or visit the site to test REST API, checkout, and custom blocks

---

## 🛠 Tools Used

- WordPress 6.5+
- WooCommerce
- JavaScript (ES6)
- PHP (WP OOP)
- HTML/CSS

---

## 🧪 Testing Instructions

- ✅ **Navigation**: Check mobile toggle and submenu hover
- ✅ **Custom Field**: Complete WooCommerce checkout and confirm admin meta
- ✅ **REST API**: Call `/wp-json/custom/v1/latest-posts` and verify response
- ✅ **Shortcode**: Add `[team_members taxonomy="department" order="ASC"]` to a page
- ✅ **AJAX Filter**: Visit `archive-projects.php` and test filter dropdown

---

## 🔒 Security Notes

- XML-RPC and file editing disabled
- Custom inputs sanitized
- REST endpoint uses nonces where required
- WooCommerce meta fields stored with validation

---

## 📜 License

GPL-2.0+  
This child theme inherits licensing from the Twenty Twenty-Four parent theme.

---

## 👤 Author

Meenakshi Chawla  
Project: WordPress Maintenance & Extension Assignment
