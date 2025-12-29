<?php
require('fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Next.js UI Roadmap - Enterprise Inventory Management System', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Ln(5);

$content = "
1. Project Setup
- Next.js 14+ (or latest stable)
- TypeScript
- Tailwind CSS
- Axios / Fetch
- React Query / SWR
- Optional: Material-UI / Ant Design / shadcn/ui

2. Pages / Routes
Authentication:
- /login – Login form
- /signup – Registration form
- /forgot-password – Request password reset
- /reset-password – Reset password form
- /profile – View/update user profile

Dashboard:
- /dashboard – Overview with:
  - Low-stock alerts (red highlights)
  - Recent purchases & sales
  - Top-selling products

Inventory Management:
- /products – List all products (table)
  - Pagination & search
  - Low-stock highlighting
  - Edit / Delete actions
- /products/add – Add product form
- /categories – List / Add / Edit categories
- /warehouses – List / Add / Edit warehouses
- /suppliers – List / Add / Edit suppliers

Transactions:
- /purchases – List purchases
  - Add purchase form
- /sales – List sales
  - Add sale form
- /stock-movements – Manual stock adjustments

3. Components
- Tables: Search, sort, pagination, conditional formatting, bulk actions
- Forms: Input validation, dropdowns
- Dashboard Widgets: Cards, graphs
- Notifications / Alerts

4. API Integration
- Use Axios or Fetch
- JWT handling
- React Query / SWR for caching

5. State Management
- React Context or Redux Toolkit

6. UI/UX Enhancements
- Responsive design
- Loading spinners / skeletons
- Modals for delete confirmations
- Multi-select filters for tables

Next Steps:
1. Set up Next.js project with Tailwind CSS
2. Implement auth pages with API integration
3. Build Dashboard page
4. Create CRUD pages for Products, Categories, Warehouses, Suppliers
5. Integrate Purchases, Sales, and Stock Movements pages
";

$pdf->MultiCell(0, 8, $content);

$pdf->Output('D', 'NextJS_UI_Roadmap_Inventory_System.pdf');
