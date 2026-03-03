# Team 23 – PixelPals  
### CS2TP Group Project Repository

---

#  Project Overview
PixelPals is a **Computer Science Team Project (CS2TP)** focused on promoting healthy, ergonomic, and accessible gaming habits for children through a designed software product and supporting website.

This repository contains all assessed work produced across **Term 1A, Term 1B, and Term 2**, including planning documentation, an MVP implementation, and the **final software product prepared for live demonstration**.

---

#  Assessment Context
This project is assessed on the following criteria:

- Software Quality & Scope  
- Team Working  
- Project Management & Process  
- Exposition & Impact  

The repository structure and documentation are designed to clearly evidence these areas.

---

#  Repository Structure
The repository is organised by academic term to clearly separate planning, MVP development, and final implementation work.

---

# Term 1A – Design & Planning
Location: `Term1A/`

Contains early-stage project planning and design artefacts, including:

- Software Requirements Document (SRD)
- Design Alliance Report
- Meeting notes and planning evidence
- Trello screenshots and contribution evidence

---

#  Term 1B – MVP Website
 Location: `Term1B/MVP/`

Contains the Minimum Viable Product (MVP) developed during Term 1B.

### Includes

**frontend/**
- HTML pages
- CSS styling
- JavaScript functionality

**backend/**
- PHP backend structure
- Configuration files

**docs/**
- Architecture overview
- RACI diagram
- MVP summary
- Testing documentation

This stage demonstrates **incremental development, version control, and quality assurance**.

---

#  Term 2 – Final Team Submission
 Location: `Team23_PixelPals_Term2_Final/`

This folder contains the **final implementation of the PixelPals software system**, prepared for both submission and live demonstration.

Includes:

- Completed PHP-based web application
- Backend logic and database integration
- Admin management system
- Customer shopping system
- Evaluation and supporting documentation

This folder is used for:

- Blackboard submission
- Final software demonstration

---

#  Final Software Architecture (Term 2)

The final system separates **frontend pages, backend logic, and database structure** for clarity and maintainability.

```
Team23_PixelPals_Term2_Final/
│
├── public/
│   │
│   ├── index.php
│   ├── about.php
│   ├── contact.php
│   ├── products.php
│   ├── product.php
│   ├── signup.php
│   ├── login.php
│   ├── logout.php
│   ├── change_password.php
│   ├── basket.php
│   ├── checkout.php
│   ├── order_success.php
│   ├── account.php
│   ├── orders.php
│   ├── order_view.php
│   │
│   ├── admin/
│   │   │
│   │   ├── dashboard.php
│   │   ├── products.php
│   │   ├── product_create.php
│   │   ├── product_edit.php
│   │   ├── stock_incoming.php
│   │   ├── orders.php
│   │   ├── order_view.php
│   │   ├── customers.php
│   │   ├── customer_edit.php
│   │   └── messages.php
│   │
│   └── assets/
│       │
│       ├── css/
│       │   └── styles.css
│       │
│       └── img/
│           ├── logo.png
│           ├── categories/
│           └── products/
│
├── app/
│   │
│   ├── config/
│   │   ├── db.php
│   │   └── config.php
│   │
│   ├── includes/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── auth.php
│   │   └── flash.php
│   │
│   └── actions/
│       │
│       ├── signup_post.php
│       ├── login_post.php
│       ├── change_password_post.php
│       │
│       ├── basket_add.php
│       ├── basket_update.php
│       ├── basket_remove.php
│       │
│       ├── checkout_place_order.php
│       ├── return_request.php
│       │
│       ├── admin_order_status_update.php
│       │
│       ├── admin_product_create.php
│       ├── admin_product_update.php
│       ├── admin_product_delete.php
│       ├── admin_stock_incoming_post.php
│       │
│       ├── review_add.php
│       │
│       ├── contact_submit.php
│       │
│       ├── account_update.php
│       ├── account_delete.php
│       │
│       ├── admin_customer_update.php
│       └── admin_customer_delete.php
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
├── README.md
└── .gitignore
```

This structure separates:

- **Frontend pages** → `public/`
- **Admin system** → `public/admin/`
- **Backend logic** → `app/actions/`
- **Shared configuration & security** → `app/config/`, `app/includes/`
- **Database schema & test data** → `database/`

---

#  File Ownership (Term 2 Implementation)

Each major system component was allocated to specific team members to ensure **clear responsibility and traceability**.

| Team Member | Responsibility |
|--------------|---------------|
| **Maeesa** | Project Manager & Backend (Authentication System) |
| **Seher** | UI/UX Designer & Frontend |
| **Russell** | Backend Lead (Database & Security) |
| **Jamaal** | Frontend & Quality Assurance |
| **Joel** | Backend & Systems Tester (Orders System) |
| **Oscar** | Frontend Lead (Shopping Interface) |
| **Toney** | Backend & Quality Assurance (Inventory System) |
| **Dia** | Content Creator & Frontend |

---

#  Authentication System
**Owner:** Maeesa

Handles user authentication and access control, along side project managing

Key components:

- User registration
- Login authentication
- Password change functionality
- Admin access protection

Backend files include:
- auth.php
- signup_post.php
- login_post.php
- change_password_post.php


---

#  UI / Layout System
**Owner:** Seher

Responsible for the visual consistency and layout across the site.

Key files:
- styles.css
- header.php
- footer.php
- index.php


Focus areas:

- Navigation structure
- Colour scheme
- Page layout consistency
- Responsive design

---

# Database & Security System
**Owner:** Russell

Responsible for the database structure and core configuration.

Files include:
- database/schema.sql
- database/seed.sql
- app/config/db.php
- app/config/config.php
- app/includes/flash.php


Admin customer management pages:
- admin/dashboard.php
- admin/messages.php
- admin/customers.php
- admin/customer_edit.php


---

#  Orders & Checkout System
**Owner:** Joel

Responsible for the complete **shopping pipeline**.

Backend logic includes:
- basket_add.php
- basket_update.php
- basket_remove.php
- checkout_place_order.php
- return_request.php
- admin_order_status_update.php


Admin order management:
- admin/orders.php
- admin/order_view.php


Customer order pages:
- orders.php
- order_view.php


---

#  Inventory Management System
**Owner:** Toney

Responsible for product and stock management.

Admin pages:
- admin/products.php
- admin/product_create.php
- admin/product_edit.php
- admin/stock_incoming.php


Backend logic:
- admin_product_create.php
- admin_product_update.php
- admin_product_delete.php
- admin_stock_incoming_post.php
- review_add.php


This system handles:

- Product creation
- Stock tracking
- Low stock alerts
- Product reviews

---

#  Customer Shopping Interface
**Owner:** Oscar

Responsible for the user shopping experience.

Pages include:
- products.php
- product.php
- basket.php
- checkout.php
- orders.php
- order_view.php


Focus areas:

- Product browsing
- Basket management interface
- Checkout user experience

---

#  Account & Authentication Pages
**Owner:** Jamaal

Responsible for user account interface pages.

Files include:
- signup.php
- login.php
- change_password.php
- account.php


---

#  Content & Static Pages
**Owner:** Dia

Responsible for informational pages and content assets.

Pages include:
- about.php
- contact.php
- order_success.php


Also responsible for:

- Product images
- Category images
- Informational content

---

#  Software Demonstration

The final software system demonstrates:

- Core website functionality
- Customer shopping experience
- Admin management system
- Database-driven product and order management
- Usability and accessibility considerations

---

#  Submission Overview

**Term 1A**
- Planning and design documentation

**Term 1B**
- MVP software development

**Term 2**
- Final system implementation
- Evaluation documentation
- Live demonstration

---

#  Project Tools & Links

Version Control  
GitHub

Task Management  
Trello  
https://trello.com/b/MgNq9DBY/team-23-pixelpalscs2tp

Repository  
https://github.com/maeesa-ali/team_23_pixelpals

---

#  Project Aim

Designing healthier gaming experiences for young players.

