# BFSG — Magento 2 Accessibility Module

> **WCAG 2.1 Level AA compliance for Magento 2 storefronts, built for the German Barrierefreiheitsstärkungsgesetz (BFSG) deadline of 28 June 2025.**

---

## Table of Contents

1. [Background & Legal Context](#1-background--legal-context)
2. [What This Module Does](#2-what-this-module-does)
3. [WCAG 2.1 Criteria Addressed](#3-wcag-21-criteria-addressed)
4. [Requirements](#4-requirements)
5. [Installation](#5-installation)
6. [Configuration](#6-configuration)
7. [Feature Reference](#7-feature-reference)
   - [7.1 Automated Accessibility Scanner](#71-automated-accessibility-scanner)
   - [7.2 ARIA Label Manager](#72-aria-label-manager)
   - [7.3 Frontend Accessibility Widget](#73-frontend-accessibility-widget)
   - [7.4 Keyboard Navigation & Focus Management](#74-keyboard-navigation--focus-management)
   - [7.5 Form Validation Enhancements](#75-form-validation-enhancements)
   - [7.6 Session Timeout Warning](#76-session-timeout-warning)
8. [Architecture & File Reference](#8-architecture--file-reference)
9. [Database Schema](#9-database-schema)
10. [JavaScript Module API](#10-javascript-module-api)
11. [CSS Classes & Customisation](#11-css-classes--customisation)
12. [Dyslexia Font Setup](#12-dyslexia-font-setup)
13. [Hyvä Theme Compatibility](#13-hyv-theme-compatibility)
14. [Known Limitations & Roadmap](#14-known-limitations--roadmap)
15. [Legal Disclaimer](#15-legal-disclaimer)
16. [License](#16-license)

---

## 1. Background & Legal Context

### The BFSG (Barrierefreiheitsstärkungsgesetz)

The **Barrierefreiheitsstärkungsgesetz** (BFSG, Federal Act on Strengthening Accessibility) is the German transposition of the **European Accessibility Act (EAA, Directive 2019/882/EU)**. It comes into force for private-sector businesses on **28 June 2025**.

The law requires that digital products and services — including **online shops** — be accessible to people with disabilities. For e-commerce, this means:

- The entire purchase process (browsing → cart → checkout → order confirmation) must be operable for users with visual, motor, cognitive, and hearing impairments.
- Non-compliance can result in fines, injunctions by competitors or consumer associations, and reputational damage.
- The standard of measure is **WCAG 2.1 Level AA** (Web Content Accessibility Guidelines), published by the W3C.

### Why Magento Shops Are Particularly Affected

Unlike a simple static website, a Magento 2 storefront poses several accessibility challenges:

| Challenge | Root cause |
|---|---|
| JavaScript-driven UI (Knockout.js) | Dynamic content changes often go unannounced to screen readers |
| Complex checkout flow | Multiple steps, form validation, shipping/payment selection |
| Merchant-controlled content | CMS pages and product descriptions may contain images without alt text or broken heading structures |
| Theme diversity | Luma, Blank, Hyvä, and custom themes each have their own markup patterns |
| Third-party widget ecosystem | Mini-carts, sliders, popups — all need ARIA annotations |

This module provides a **pragmatic "overlay" approach**: core fixes are applied via JavaScript mixins and CSS injections, so the module works across Luma-compatible themes without requiring template surgery. Deeper structural fixes — recommended for full compliance — are noted in the [Known Limitations](#14-known-limitations--roadmap) section.

---

## 2. What This Module Does

`Zwernemann_BFSG` is a **self-contained, locally hosted accessibility layer** for Magento 2. It provides six interconnected features, all configurable from the Magento Admin:

| # | Feature | Admin location |
|---|---|---|
| 1 | Automated content accessibility scanner | BFSG Accessibility → Accessibility Check |
| 2 | ARIA Label Manager (no template edits) | BFSG Accessibility → ARIA Label Manager |
| 3 | Frontend accessibility widget (contrast, font, dyslexia) | via Stores → Configuration |
| 4 | Keyboard navigation & focus trap | automatic, JS module |
| 5 | Form validation ARIA enhancements | automatic, JS mixin |
| 6 | Session timeout warning dialog | via Stores → Configuration |

**No data is sent to any external server.** All user preferences are stored in the browser's `localStorage`. No cookies are set. The module is fully GDPR-compliant by design.

---

## 3. WCAG 2.1 Criteria Addressed

The table below maps each module feature to the specific WCAG 2.1 success criterion it targets.

| WCAG SC | Level | Name | Feature |
|---|---|---|---|
| **1.1.1** | A | Non-text Content | Scanner detects missing `alt` on `<img>` |
| **1.3.1** | A | Info and Relationships | Scanner detects heading hierarchy skips; form `fieldset`/`legend` guidance |
| **1.4.3** | AA | Contrast (Minimum) | High contrast mode CSS toggle |
| **1.4.4** | AA | Resize Text | Font size widget (80%–160%, reflow without horizontal scroll) |
| **2.1.1** | A | Keyboard | Focus trap keeps Tab within open overlays/modals |
| **2.2.1** | A | Timing Adjustable | Session timeout warning with keyboard-accessible "Stay Logged In" |
| **2.4.3** | A | Focus Order | Focus trap; focus moved to first invalid field on form error |
| **2.4.7** | AA | Focus Visible | `.bfsg-keyboard-nav` class enforces visible 3 px outline for keyboard users |
| **3.3.1** | A | Error Identification | `aria-invalid="true"` added to invalid form fields |
| **3.3.2** | A | Labels or Instructions | ARIA Label Manager links description elements via `aria-describedby` |
| **3.3.3** | AA | Error Suggestion | Error text linked to field via `aria-describedby` |
| **4.1.2** | A | Name, Role, Value | ARIA Label Manager injects `aria-label`/`aria-describedby` on any element |
| **4.1.3** | AA | Status Messages | `aria-live="assertive"` announces validation errors; `role="status"` on polite announcements |

---

## 4. Requirements

| Requirement | Version |
|---|---|
| Magento Open Source / Adobe Commerce | 2.4.4 or later |
| PHP | 8.1 or later |
| Composer | 2.x |
| Theme | Luma, Blank, or any Luma-compatible theme |
| Database | MySQL 8.0 / MariaDB 10.4 |

> **Hyvä Compatibility:** The JavaScript modules use RequireJS/jQuery, which are not loaded in Hyvä by default. See [Section 13](#13-hyv-theme-compatibility) for integration guidance.

---

## 5. Installation

### Via Composer (recommended)

```bash
composer require zwernemann/magento2-bfsg
bin/magento module:enable Zwernemann_BFSG
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Manual Installation

1. Copy the module directory into `app/code/Zwernemann/BFSG/`.
2. Run the same Magento CLI commands as above.

### Post-Installation Checklist

- [ ] Navigate to **Stores → Configuration → BFSG Accessibility** and enable the module.
- [ ] Run the accessibility scanner (**BFSG Accessibility → Accessibility Check**) and address reported errors.
- [ ] Add `aria-label` mappings for checkout buttons, filter controls, and search fields via the ARIA Label Manager.
- [ ] (Optional) Supply the OpenDyslexic font files — see [Section 12](#12-dyslexia-font-setup).
- [ ] Test the frontend with a screen reader (NVDA + Firefox or VoiceOver + Safari).

---

## 6. Configuration

All settings are found under **Stores → Configuration → BFSG Accessibility**.

### General

| Field | Default | Description |
|---|---|---|
| Enable Module | Yes | Master switch. Disabling stops all frontend output and hides admin features. |

### Accessibility Widget

| Field | Default | Description |
|---|---|---|
| Enable Widget | Yes | Renders the floating accessibility toolbar on all frontend pages. |
| Widget Position | Bottom Right | One of: Bottom Right, Bottom Left, Top Right, Top Left. |
| Show High Contrast Toggle | Yes | Adds the contrast switcher to the widget panel. |
| Show Font Size Controls | Yes | Adds the A− / A / A+ buttons to the widget panel. |
| Show Dyslexia Font Toggle | Yes | Adds the dyslexia-friendly font switcher. |

### Session Timeout Warning

| Field | Default | Description |
|---|---|---|
| Warning Before Timeout (seconds) | 120 | How many seconds before the Magento session expires the warning dialog is shown. Set to 0 to disable. |

All configuration values are store-view scoped, so you can configure the module differently per store view or website.

---

## 7. Feature Reference

### 7.1 Automated Accessibility Scanner

**Admin path:** BFSG Accessibility → Accessibility Check

The scanner performs a static HTML analysis of your store's content using PHP's `DOMDocument` and `DOMXPath`. It checks:

#### What is checked

| Check | Rule | Severity |
|---|---|---|
| `<img>` elements without an `alt` attribute | WCAG 1.1.1 | Error |
| Heading hierarchy skips (e.g. `<h1>` followed directly by `<h3>`) | WCAG 1.3.1 | Warning |
| `<a>` elements with no accessible name (no text content, no `aria-label`, no `title`) | WCAG 4.1.2 | Error |

#### Scope

- **CMS Pages:** All pages from `cms_page` table, checking the `content` field.
- **Products:** First 200 products (by collection order), checking `description` and `short_description` attributes.

Use the **Scan CMS Pages Only** or **Scan Products Only** buttons to limit scope in large stores, then use **Scan Everything** for a comprehensive audit.

#### Results table

Results are displayed as a table grouped by item (CMS page or product). Each row shows:
- Item type, name, and identifier (URL key / SKU)
- Severity (ERROR / WARNING)
- WCAG rule number
- Human-readable description of the issue

#### Extending the checker

The `ContentChecker` model (`Model/Checker/ContentChecker.php`) is a plain PHP class with no Magento-specific dependencies beyond the two collection factories. You can add custom checks by:

1. Subclassing `ContentChecker` and overriding `analyzeHtml()`.
2. Using a virtual type / `di.xml` preference to swap the implementation.

---

### 7.2 ARIA Label Manager

**Admin path:** BFSG Accessibility → ARIA Label Manager

The ARIA Label Manager solves a common agency pain point: **adding ARIA attributes to Magento elements without touching `.phtml` or Knockout templates**.

#### How it works

1. In the admin, you define a **CSS selector** (e.g. `#search`, `.action-tocart`, `button[data-role="proceed-to-checkout"]`) and the desired `aria-label` or `aria-describedby` value.
2. On page load, `accessibility-widget.js` queries the active labels from the PHP block and applies them to matching DOM elements — but **only if the element does not already have the attribute** (so it never overwrites intentional template markup).
3. Labels are scoped by **store view** (0 = all stores).

#### When to use `aria-label` vs `aria-describedby`

| Attribute | Use when |
|---|---|
| `aria-label` | The element has no visible text label at all, or the visible text is insufficient for screen reader users (e.g. an icon-only button). |
| `aria-describedby` | An additional description element already exists on the page (e.g. a hint paragraph), and you want to link it to the control. |

#### Example: Magento checkout buttons

| Selector | aria-label |
|---|---|
| `button.action.checkout` | `Proceed to checkout` |
| `button[data-role="proceed-to-checkout"]` | `Proceed to secure checkout` |
| `#search` | `Search products` |
| `.filter-options-title` | (leave empty, use aria-describedby to link hint) |
| `.action.towishlist` | `Add to Wish List` |

#### Database storage

Labels are stored in `bfsg_aria_label` (see [Section 9](#9-database-schema)). The collection is filtered to active records for the current store view, serialised to JSON, and embedded in the widget template as inline config — **one database query per page, no AJAX**.

---

### 7.3 Frontend Accessibility Widget

**File:** `view/frontend/web/js/accessibility-widget.js`

The widget is a **locally hosted, GDPR-compliant alternative** to third-party SaaS accessibility overlays like UserWay or accessiBe. No external scripts are loaded; no data leaves the browser.

#### High Contrast Mode (WCAG 1.4.3)

Toggling "High Contrast" adds the class `bfsg-high-contrast` to `<body>`. The accompanying CSS (`accessibility.css`) then overrides:

- Background: `#000`, Foreground: `#fff`
- Links: `#ffff00` (visited: `#ff9900`)
- Buttons: yellow background, black text, white border
- Form inputs: black background, white text, white border
- Images: CSS `invert(1)` filter (mark individual images with `.bfsg-high-contrast-ignore` to exclude them)

The contrast ratio of white on black is 21:1, far exceeding the WCAG 1.4.3 minimum of 4.5:1 for normal text.

#### Font Size Controls (WCAG 1.4.4)

Font size is adjusted by modifying `document.documentElement.style.fontSize` in 10% increments:

- Minimum: 80% of the base size
- Default: 100%
- Maximum: 160%

Because all Magento Luma/Blank theme measurements use `em`/`rem` units relative to the root, this single property cascades through the entire page. The CSS sets `overflow-x: hidden` on `<html>` and `word-wrap: break-word` on `<body>` to prevent horizontal scrollbars at large sizes — meeting WCAG 1.4.4's "Reflow" requirement.

#### Dyslexia-Friendly Font

Toggling "Dyslexia Font" adds `bfsg-dyslexia-font` to `<body>`, which applies:

```css
font-family: 'OpenDyslexic', 'Comic Sans MS', 'Trebuchet MS', Verdana, sans-serif;
letter-spacing: 0.05em;
word-spacing: 0.1em;
line-height: 1.6;
```

OpenDyslexic is a free, open-source typeface specifically designed to mitigate common dyslexia-related reading errors (letter confusion, visual distortion). The module references local font files — see [Section 12](#12-dyslexia-font-setup) for setup.

#### Preference Persistence

All three settings are saved to `localStorage` under the key `bfsg_prefs` as a JSON object:

```json
{ "contrast": true, "fontSize": 120, "dyslexia": false }
```

Preferences are restored on the next page load. `localStorage` is origin-scoped and never transmitted to the server.

#### Widget Panel Accessibility

The widget panel itself is fully keyboard-accessible:

- Toggle button has `aria-expanded` and `aria-controls`
- Panel has `role="dialog"` and `aria-label`
- Focus is trapped inside the panel when open (via `focus-manager.js`)
- `Escape` key closes the panel and returns focus to the toggle button
- All action buttons have descriptive `aria-label` or `aria-pressed` attributes

---

### 7.4 Keyboard Navigation & Focus Management

**File:** `view/frontend/web/js/focus-manager.js`

This module is loaded as a dependency of `accessibility-widget.js` and auto-initialises on load.

#### Focus Trap (WCAG 2.1.1 / 2.4.3)

When `focusManager.trapFocus(containerElement)` is called (e.g. when the widget panel or session dialog opens):

1. A `keydown` listener is added at capture phase on `document`.
2. On `Tab`, the list of focusable elements inside the container is queried.
3. If focus is on the **last** element, it wraps forward to the **first**.
4. If focus is on the **first** element and `Shift+Tab` is pressed, it wraps backward to the **last**.

This prevents the keyboard "escape" problem described in WCAG 2.1.1. Call `focusManager.releaseFocus()` when the container closes to remove the listener.

Focusable elements are matched by the selector:
```
a[href], area[href], button:not([disabled]), input:not([disabled]),
select:not([disabled]), textarea:not([disabled]),
[tabindex]:not([tabindex="-1"]), details > summary
```

#### Keyboard-Navigation Focus Ring (WCAG 2.4.7)

Magento's default Luma theme often suppresses or minimises the `:focus` outline (sometimes deliberately, for visual design reasons), leaving keyboard-only users without a visible cursor.

`focus-manager.js` detects keyboard navigation by listening for `Tab`, `Enter`, and `Space` keydown events. When detected:

- `bfsg-keyboard-nav` is added to `document.body`
- CSS applies a high-visibility 3 px blue outline (`#005fcc`) with a `box-shadow` halo to all `:focus` elements

When the user clicks with a mouse, `bfsg-keyboard-nav` is removed and focus outlines revert to the browser default (typically invisible for mouse users). This pattern mirrors the `:focus-visible` pseudo-class and is backwards-compatible with older browsers.

---

### 7.5 Form Validation Enhancements

**File:** `view/frontend/web/js/form-accessibility.js`

This module is applied as a **RequireJS mixin** on Magento's `mage/validation` module (registered in `requirejs-config.js`). It wraps two internal methods using Magento's `mage/utils/wrapper` utility.

#### `showLabel` wrapper — per-field ARIA (WCAG 3.3.1 / 3.3.3 / 4.1.2)

Called by `mage/validation` whenever a field validation error is shown or hidden.

**On error:**
1. Sets `aria-invalid="true"` on the field.
2. Generates a deterministic ID (`bfsg-error-{field-id}`) and assigns it to the `.mage-error` element.
3. Adds `role="alert"` to the error element so it is announced immediately.
4. Appends the error element's ID to the field's `aria-describedby` attribute (preserving any existing value).

**On clear:**
1. Removes `aria-invalid`.
2. Strips the injected ID from `aria-describedby`.

#### `invalidHandler` wrapper — form-level announcement (WCAG 4.1.3 / 2.4.3)

Called once when form submission fails validation.

1. Counts total invalid fields.
2. Announces *"Please correct N error(s) in the form to continue."* via `aria-live="assertive"` — this is read immediately by screen readers without waiting for the next focus change.
3. After a 100 ms delay (to allow the DOM to settle), moves keyboard focus to the first invalid field.

#### Example — before and after

**Before (standard Magento):**
```html
<input id="email" type="email" class="input-text required-entry" />
<div class="mage-error">Please enter a valid email address.</div>
```
The screen reader user submits the form. Nothing is announced. The error div appears visually but is invisible to AT.

**After (with BFSG mixin):**
```html
<input id="email" type="email" class="input-text required-entry"
       aria-invalid="true"
       aria-describedby="bfsg-error-email" />
<div class="mage-error" id="bfsg-error-email" role="alert">
    Please enter a valid email address.
</div>
```
The screen reader immediately announces the error count, then moves to and reads the first invalid field along with its error message.

---

### 7.6 Session Timeout Warning

**File:** `view/frontend/web/js/session-timeout.js`

WCAG 2.2.1 (*Timing Adjustable*) requires that if a time limit is set by the content, users must be able to turn off, adjust, or extend it. Magento's default session expiry provides no warning.

#### How the timer works

1. On load, the module attempts to read the remaining session lifetime from the `mage-cache-timeout` cookie (set by Magento's customer section system). Falls back to 3600 s if not found.
2. Two timers are set:
   - **Warning timer:** fires `warningBeforeExpiry` seconds before the calculated expiry.
   - **Expiry timer:** fires at the calculated expiry, redirecting to logout.
3. User interactions (mouse, keyboard, scroll) update an `_lastActivity` timestamp. This is used as a signal that the session was extended by server-side activity; the module reschedules itself after a successful "Stay Logged In" ping.

#### The warning dialog

The warning is rendered as a `role="alertdialog"` with `aria-modal="true"`, `aria-labelledby`, and `aria-describedby` — meeting the ARIA authoring practices for alert dialogs. Focus is trapped inside it via `focus-manager.js`.

Two actions are available:

| Button | Action |
|---|---|
| Stay Logged In | Fires a `$.get()` to `customer/account/` (a lightweight, authenticated endpoint). On success, re-schedules all timers based on the new session lifetime. |
| Log Out | Redirects to `customer/account/logout/`. |

Pressing `Escape` is equivalent to "Stay Logged In" (the safer default).

---

## 8. Architecture & File Reference

```
Zwernemann/BFSG/
│
├── registration.php                          Module registration
│
├── etc/
│   ├── module.xml                            Module declaration & sequence
│   ├── config.xml                            Default configuration values
│   ├── acl.xml                               ACL resource tree
│   ├── db_schema.xml                         Declarative DB schema
│   └── adminhtml/
│       ├── routes.xml                        Admin router (frontName: bfsg)
│       ├── menu.xml                          Admin menu items
│       └── system.xml                        Stores → Configuration section
│
├── Model/
│   ├── AriaLabel.php                         Magento model (flat table)
│   ├── Config.php                            Typed config value accessor
│   ├── Config/Source/Position.php           Widget position select source
│   ├── Checker/
│   │   └── ContentChecker.php               HTML accessibility scanner
│   └── ResourceModel/
│       ├── AriaLabel.php                     Resource model
│       └── AriaLabel/Collection.php          Collection
│
├── Controller/Adminhtml/
│   ├── Check/
│   │   ├── Index.php                         Scanner dashboard page
│   │   └── Run.php                           AJAX scan endpoint (JSON)
│   └── AriaLabel/
│       ├── Index.php                         Grid listing
│       ├── NewAction.php                     New record form
│       ├── Edit.php                          Edit existing record
│       ├── Save.php                          POST save handler
│       └── Delete.php                        Delete handler
│
├── Block/
│   ├── Adminhtml/
│   │   ├── Check/Index.php                   Scanner UI block
│   │   └── AriaLabel/
│   │       ├── Grid.php                      Grid listing block
│   │       └── Edit/Form.php                 Edit form block
│   └── Frontend/
│       └── AccessibilityWidget.php           Widget block (config + JSON)
│
└── view/
    ├── adminhtml/
    │   ├── layout/
    │   │   ├── bfsg_adminhtml_check_index.xml
    │   │   ├── bfsg_adminhtml_arialabel_index.xml
    │   │   ├── bfsg_adminhtml_arialabel_new.xml
    │   │   └── bfsg_adminhtml_arialabel_edit.xml
    │   └── templates/
    │       ├── check/index.phtml             Scanner UI + inline JS
    │       └── arialabel/
    │           ├── grid.phtml                Records table
    │           └── form.phtml                Create / edit form
    │
    └── frontend/
        ├── layout/
        │   └── default.xml                   Injects CSS + widget block on all pages
        ├── requirejs-config.js               Module map + mage/validation mixin
        ├── templates/
        │   └── widget.phtml                  Widget HTML + x-magento-init
        └── web/
            ├── css/
            │   └── accessibility.css         All BFSG frontend styles
            └── js/
                ├── accessibility-widget.js   Widget controller + ARIA injection
                ├── focus-manager.js          Focus trap + keyboard detection
                ├── form-accessibility.js     mage/validation mixin
                └── session-timeout.js        Session warning dialog
```

### Data flow diagram

```
Magento Admin
  └─ Stores → Config ──────────────────────────────────► Model/Config.php
                                                               │
  └─ BFSG → ARIA Label Manager ──► bfsg_aria_label table       │
                                           │                   │
                                           ▼                   ▼
                              Block/Frontend/AccessibilityWidget.php
                                           │
                              (JSON serialised into page)
                                           │
                              ┌────────────▼────────────────────────┐
                              │  view/frontend/templates/widget.phtml│
                              │  x-magento-init config object        │
                              └────────────┬────────────────────────┘
                                           │ RequireJS
                              ┌────────────▼────────────────────────┐
                              │  js/accessibility-widget.js          │
                              │    ├── applies ARIA labels to DOM    │
                              │    ├── widget open/close + prefs     │
                              │    └── calls session-timeout.js      │
                              └─────────────────────────────────────┘

  Page load (all pages)
    └─ js/focus-manager.js ──── keyboard detection (auto-init)
    └─ js/form-accessibility.js ── mage/validation mixin (auto-applied)
```

---

## 9. Database Schema

The module creates one table via Magento's declarative schema system (`etc/db_schema.xml`).

### `bfsg_aria_label`

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| `entity_id` | INT UNSIGNED | No | AUTO_INCREMENT | Primary key |
| `element_selector` | VARCHAR(255) | No | — | CSS selector targeting the element(s) |
| `element_type` | VARCHAR(64) | No | — | Descriptive type (button, input, link …) |
| `aria_label` | VARCHAR(255) | Yes | NULL | Value for the `aria-label` attribute |
| `aria_describedby` | VARCHAR(255) | Yes | NULL | ID of the describing element |
| `store_id` | SMALLINT UNSIGNED | No | 0 | Store view (0 = all stores) |
| `is_active` | SMALLINT | No | 1 | 1 = active, 0 = disabled |
| `created_at` | TIMESTAMP | No | CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | No | CURRENT_TIMESTAMP | Last update (ON UPDATE) |

**Indexes:** `store_id`, `is_active`

The table is lightweight by design. In a typical installation, you might have 20–80 ARIA label mappings.

---

## 10. JavaScript Module API

### `Zwernemann_BFSG/js/focus-manager`

```js
// Trap Tab focus within a container
focusManager.trapFocus(HTMLElement containerElement);

// Release the active focus trap
focusManager.releaseFocus();
```

Auto-initialises keyboard navigation detection on `require()`.

---

### `Zwernemann_BFSG/js/session-timeout`

```js
sessionTimeout.init({
    warningBeforeExpiry: 120  // seconds before session end
});
```

---

### `Zwernemann_BFSG/js/accessibility-widget`

Initialised via `x-magento-init` in `widget.phtml`. Config object structure:

```json
{
  "position":       "bottom-right",
  "showContrast":   true,
  "showFontSize":   true,
  "showDyslexia":   true,
  "sessionWarning": 120,
  "ariaLabels": [
    {
      "selector":        "#search",
      "ariaLabel":       "Search products",
      "ariaDescribedby": "search-hint"
    }
  ]
}
```

---

### `Zwernemann_BFSG/js/form-accessibility`

Applied automatically as a mixin. No public API; works transparently alongside any `mage/validation`-based form.

---

## 11. CSS Classes & Customisation

### State classes (applied to `<body>`)

| Class | Applied when |
|---|---|
| `bfsg-high-contrast` | High contrast mode is active |
| `bfsg-dyslexia-font` | Dyslexia font is active |
| `bfsg-keyboard-nav` | User is navigating by keyboard |

You can hook into these classes in your theme's CSS for additional per-state overrides:

```css
/* Example: darken hero images in high-contrast mode */
body.bfsg-high-contrast .hero-image {
    filter: invert(1) hue-rotate(180deg) brightness(0.8);
}

/* Example: widen line-height further in dyslexia mode */
body.bfsg-dyslexia-font .product-info-main {
    line-height: 2;
}
```

### Utility class

| Class | Purpose |
|---|---|
| `bfsg-sr-only` | Visually hides an element while keeping it accessible to screen readers. Safe replacement for `display:none`. |
| `bfsg-high-contrast-ignore` | Exempts an image from the CSS `invert()` filter in high-contrast mode. |

### Widget element selectors

| Selector | Element |
|---|---|
| `#bfsg-widget` | Widget root container |
| `#bfsg-toggle` | Toggle button |
| `#bfsg-panel` | Options panel |
| `#bfsg-session-warning` | Session timeout dialog |
| `#bfsg-aria-live-polite` | Polite screen-reader announcement region |
| `#bfsg-aria-live-assertive` | Assertive screen-reader announcement region |

Override widget colours in your theme's CSS (higher specificity wins):

```css
/* Example: match widget button to brand colour */
.bfsg-widget__toggle {
    background: #e40046;
}
.bfsg-widget__toggle:hover,
.bfsg-widget__toggle:focus {
    background: #b3003a;
}
.bfsg-widget__header {
    background: #e40046;
}
```

---

## 12. Dyslexia Font Setup

The module references OpenDyslexic via a local `@font-face` declaration in `accessibility.css`:

```css
@font-face {
    font-family: 'OpenDyslexic';
    src: url('../fonts/OpenDyslexic-Regular.otf') format('opentype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
```

You must supply the font file yourself. OpenDyslexic is free and open-source:

1. Download from [https://opendyslexic.org](https://opendyslexic.org) or the [GitHub releases](https://github.com/antijingoist/opendyslexic/releases).
2. Place `OpenDyslexic-Regular.otf` (and optionally Bold, Italic variants) in:
   ```
   view/frontend/web/fonts/
   ```
3. Run `bin/magento setup:static-content:deploy` to publish the font file.

If the font file is absent, the browser silently falls back to the CSS stack (`Comic Sans MS → Trebuchet MS → Verdana → sans-serif`), which still provides improved readability compared to typical sans-serif fonts.

---

## 13. Hyvä Theme Compatibility

Hyvä does not use RequireJS or the standard `window.jQuery`. The JavaScript modules in this extension therefore cannot be used as-is with Hyvä.

### Recommended integration approach for Hyvä

1. **Do not enable `bfsg/widget/enabled`** in the Magento configuration when using Hyvä.
2. Copy the logic from the four JS files into **Alpine.js components** or **plain ES modules** loaded via Hyvä's `HyvaEvents` system.
3. The PHP backend (scanner, ARIA Label Manager, configuration) works identically regardless of theme.
4. The CSS (`accessibility.css`) is largely theme-agnostic and can be imported in your Hyvä `tailwind.config.js` source paths or loaded as a separate stylesheet.

A dedicated Hyvä compatibility module is planned — see [Section 14](#14-known-limitations--roadmap).

---

## 14. Known Limitations & Roadmap

### Current limitations

| Limitation | Detail |
|---|---|
| **Scanner scope** | Products collection is capped at 200 items. Stores with thousands of products need the CLI command (planned). |
| **Scanner false negatives** | HTML rendered by JavaScript (e.g. Knockout templates) is not scanned — only static HTML from database fields. |
| **Structural ARIA** | The mixin enhances validation errors but does not add `fieldset`/`legend` to the shipping/payment radio groups. This requires template-level changes. |
| **Session cookie detection** | `mage-cache-timeout` is a client-set cookie that may not accurately reflect the actual server-side session remaining time in all Magento setups. |
| **Hyvä** | No native Hyvä support in this release. |
| **Checkout mixins** | Only `mage/validation` is mixed. Knockout-based checkout steps (address, shipping, payment) may need individual mixins for full coverage. |

### Planned features

- [ ] CLI command `bfsg:check` for full-store scans and CI integration
- [ ] Hyvä compatibility companion module (`Zwernemann_BFSGHyva`)
- [ ] PDF report export for scanner results
- [ ] Colour contrast ratio checker for CSS custom properties
- [ ] ARIA role manager (add/override `role` attribute alongside `aria-label`)
- [ ] Skip-navigation link injector
- [ ] Automated `fieldset`/`legend` wrapper for checkout radio groups
- [ ] Integration with Magento's message block to add `role="status"` / `role="alert"`

---

## 15. Legal Disclaimer

This module is a **technical tool to support WCAG 2.1 compliance efforts**. It does not guarantee full legal compliance with the BFSG, the European Accessibility Act, or any other regulation.

Full accessibility compliance requires:

- A thorough manual audit by accessibility experts
- Testing with real users who have disabilities
- Testing with assistive technologies (NVDA, JAWS, VoiceOver, TalkBack)
- Addressing structural markup issues in templates and themes
- An up-to-date **Accessibility Statement** (Barrierefreiheitserklärung) published on your website

**For legal advice, consult a qualified attorney specialising in e-commerce and accessibility law.**

---

## 16. License

MIT License

Copyright (c) 2025 Zwernemann

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

---

*Built with care for the 7.5 million people with disabilities in Germany who deserve the same frictionless online shopping experience as everyone else.*
