# SCSS Setup Guide

## Overview

This project includes SCSS support using Gulp as the task runner.

## Project Structure

```
assets/
└── scss/                 # SCSS source files
    ├── main.scss         # Main SCSS entry point
    ├── _variables.scss   # Variables (colors, fonts, spacing)
    ├── _mixins.scss      # Reusable Sass mixins
    ├── _base.scss        # Reset and base styles
    ├── components/       # Component styles
    │   ├── _header.scss
    │   └── _pagination.scss
    ├── layout/           # Layout styles
    │   └── _main.scss
    └── pages/            # Page-specific styles
        └── _home.scss

public/assets/
└── css/                  # Compiled CSS (generated)
    ├── main.css          # Compiled stylesheet
    └── main.css.map      # Sourcemap for development
```

## Installation

1. Install Node.js dependencies:
    ```bash
    npm install
    ```

## Development Workflow

1. Watch SCSS files for changes and auto-compile:

    ```bash
    npm run watch
    ```

    Gulp will watch for changes to `.scss` files and recompile automatically with sourcemaps. Output goes to `public/assets/css/`.

2. Edit SCSS files in `assets/scss/` - they compile automatically when you save.

## Compile Once

To compile SCSS without watching:

```bash
npm run sass
```

## Production Build

Build minified CSS for production:

```bash
npm run build
```

This compiles and minifies all SCSS files (no sourcemaps) to `public/assets/css/`.

## Using the CSS in Your PHP Views

Link the compiled CSS in your main layout file ([app/Views/layouts/main.php](app/Views/layouts/main.php)):

```php
<head>
    <!-- Other head content -->
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
```

## File Organization Best Practices

- **\_variables.scss**: Define colors, fonts, spacing, breakpoints
- **\_mixins.scss**: Reusable mixins for common patterns
- **\_base.scss**: Global reset, typography, basic elements
- **components/**: Individual component styles (header, buttons, cards, etc.)
- **layout/**: Layout-related styles (containers, grids, etc.)
- **pages/**: Page-specific overrides or unique styles

## Responsive Design

Use the `respond-to` mixin for responsive breakpoints:

```scss
.element {
    width: 100%;

    @include respond-to("md") {
        width: 50%;
    }

    @include respond-to("lg") {
        width: 33%;
    }
}
```

Available breakpoints: `sm`, `md`, `lg`, `xl`

## Example Component Style

Create new component styles by adding files to `assets/scss/components/`:

```scss
// assets/scss/components/_button.scss
.btn {
    padding: $spacing-sm $spacing-md;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    @include transition(background-color);
    cursor: pointer;

    &:hover {
        background-color: darken($primary-color, 10%);
    }

    &.btn-primary {
        background-color: $primary-color;
        color: white;
    }
}
```

Then import it in `main.scss`:

```scss
@import "components/button";
```

## Troubleshooting

- **CSS not updating**: Make sure Gulp watch is running (`npm run watch`)
- **CSS not applying**: Check that the compiled CSS is linked in your PHP layout
- **Build errors**: Run `npm run sass` to see detailed error messages (or check the terminal output from the watch command)
