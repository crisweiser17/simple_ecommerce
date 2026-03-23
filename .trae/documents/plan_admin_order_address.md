# Plan: Order Address Grouping, Complement Field, and Admin Translation Fixes

## 1. Database Schema Updates
- **Action**: Create a one-time PHP script `update_db_complement.php` to add `customer_complement` to `orders` and `complement` to `users` tables using SQLite `ALTER TABLE`.
- **Action**: Also update `setup_db.php` schema to include these fields for future fresh installs.

## 2. Backend Logic Updates (`src/`)
- **`src/orders.php`**:
  - Update `createOrder()`: Add `customer_complement` to the `INSERT` query and parameter list.
  - Update `updateOrder()`: Add `customer_complement` to the `$fields` and `$values` dynamically built array.
- **`src/user.php`**:
  - Update `updateUser()`: Add `'complement'` to the `$allowed` fields array so users can update it.

## 3. Checkout and Account Logic (`index.php`)
- **Route `/checkout` (POST)**:
  - Extract `$_POST['complement']` and add to `$customer` array.
  - Update the `$addressParts` builder to include the complement if it's provided.
- **Route `/account` (POST)**:
  - Add `'complement' => trim($_POST['complement'] ?? '')` to the `$data` array so it can be saved in the user profile.

## 4. Frontend Templates Updates (`templates/`)
- **`templates/cart.php` (Checkout Form)**:
  - Add an input field for "Complement" (`name="complement"`) next to the other address fields in the "Delivery Address" section.
- **`templates/account.php` (Profile Update Form)**:
  - Add an input field for "Complement" (`name="complement"`) in the "Delivery Address" grid.
- **`templates/admin/order_detail.php`**:
  - **Translation Fixes**: Replace hardcoded English labels (Street, Number, Neighborhood, City, State, WhatsApp) with `<?php echo __('Label'); ?>` so they respect the selected language.
  - **Add Complement Field**: Add `customer_complement` to the Alpine.js `order` data model and the form layout.
  - **Grouped Address Display (Exibição)**: 
    - Replace the basic `textarea` for "Full Address String" with a nicely formatted read-only block.
    - Format requested:
      ```
      Nome Completo
      Street, Number
      Neighborhood - Complemento
      City State Zip
      ```
    - Add a "Copy to Clipboard" button next to this grouped display using Alpine.js (`navigator.clipboard.writeText(...)`).
    - The Alpine.js model will compute this full address string dynamically so it reflects any live edits made by the admin.

## 5. Translation Files (`lang/en.php` and `lang/pt.php`)
- Add/update translations for:
  - 'Complement' => 'Complemento'
  - 'Address Details' => 'Detalhes do Endereço'
  - 'Copy Address' => 'Copiar Endereço'
  - 'Copied!' => 'Copiado!'
  - 'Full Address (Copy for labels)' => 'Endereço Completo (Copiar para etiquetas)'
  - Ensure 'Street', 'Number', 'Neighborhood', 'City', 'State' are correctly mapped in `lang/pt.php`.