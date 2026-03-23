# Plan: Add "Back to Email" option in Token Login flows

The user requested an option to return to the email input screen after a login token is sent on the cart, login, and checkout pages, allowing the visitor to enter a different email address if needed.

Based on the project structure, the token login flow is present in two main files:
1. `templates/login.php`
2. `templates/cart.php` (which acts as the main checkout flow and handles the authentication sidebar)
*(Note: `checkout_express.php` and `product_single.php` use a direct PIX flow without login tokens).*

## Implementation Steps

### 1. Update `templates/cart.php`
- **Component Logic:** In the Alpine.js `requestToken()` function, add `this.loginToken = '';` to clear any previously entered token when a new one is requested.
- **UI Element:** Inside the block `x-show="tokenSent"`, add a "Back to Email" button just below the "Resend code" button. 
  - Code: `<button @click="tokenSent = false; loginToken = '';" class="text-xs text-gray-500 hover:text-gray-700 underline mt-2"><?php echo __('Back to Email'); ?></button>`
  - This button will hide the token input and show the email input, clearing the token field in the process.

### 2. Update `templates/login.php`
- **Component Logic:** In the Alpine.js `requestToken()` function, add `this.loginToken = '';` to reset the token field whenever a fresh token is requested.
- **UI Element:** The "Back to Email" button already exists in this file. We will enhance its behavior to also clear the token.
  - Change `<button @click="tokenSent = false" ...>` to `<button @click="tokenSent = false; loginToken = '';" ...>`

This plan ensures a consistent and user-friendly authentication flow across the site.