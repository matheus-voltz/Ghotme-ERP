# Ghotme ERP

Ghotme ERP is a modern, modular and scalable **SaaS ERP platform** built with **Laravel**, designed to adapt to multiple business segments such as workshops, clinics, retail stores and service companies.

The project focuses on clean architecture, real-world business workflows and multi-tenant scalability, offering a flexible foundation for growing companies.

---

## 🚀 Key Features

* Modular ERP architecture
* Multi-tenant ready (SaaS)
* Orders / Services / Sales management
* Customers and suppliers management
* Inventory control
* Financial management (payables, receivables, cash flow)
* Customizable workflows per business type
* API-first approach (ready for mobile apps)
* Built with Laravel best practices

---

## 🧱 Tech Stack

* **Backend:** Laravel
* **Database:** MySQL / PostgreSQL
* **Authentication:** Laravel Auth / Sanctum
* **Queues & Jobs:** Laravel Queues
* **Storage:** Local / S3 compatible
* **CI/CD:** GitHub Actions (planned)

---

## 🏗 Architecture Overview

Ghotme ERP follows a clean and scalable architecture:

* Controllers handle HTTP requests
* Services contain business logic
* Repositories abstract database access
* Multi-tenant layer isolates company data
* API resources standardize responses

This structure allows easy maintenance, testing and future expansion.

---

## 📂 Project Structure

```
app/
 ├── Http/
 │   ├── Controllers/
 │   ├── Middleware/
 │   └── Requests/
 ├── Models/
 ├── Services/
 ├── Repositories/
 └── Jobs/

routes/
 ├── api.php
 └── web.php

database/
 ├── migrations/
 ├── seeders/
 └── factories/

docs/
```

---

## 🧩 Multi-Tenancy

The system is designed with **multi-tenant support**, allowing multiple companies to use the platform securely, each with isolated data and configurations.

Tenant identification strategies:

* `tenant_id` per table
* Subdomain or domain-based resolution

---

## 🛣 Roadmap

* [x] Core Laravel structure
* [x] Authentication and base permissions
* [x] Multi-tenant foundation
* [ ] Inventory module
* [ ] Financial module
* [ ] Advanced reports
* [ ] Mobile application
* [ ] Public API documentation

---

## 🎯 Vision

Ghotme ERP aims to become a single management platform capable of adapting to different business realities, reducing operational complexity and helping companies scale with control and clarity.

---

## 📄 License

This project is licensed under the MIT License.
