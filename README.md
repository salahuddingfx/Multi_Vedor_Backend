# <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Gear.png" alt="Gear" width="45" /> Nexus Core | Multi-Vendor API Engine

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&weight=600&size=30&pause=1000&color=FF2D20&center=true&vCenter=true&width=700&height=70&lines=High-Performance+Laravel+Infrastructure;Sub-20ms+Intelligent+Aggregation;Real-time+Event+Broadcasting+System" alt="Typing SVG" />
</p>

---

## 🛠️ Tech Stack & Infrastructure
<p align="center">
  <a href="https://laravel.com" target="_blank"><img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" /></a>
  <a href="https://www.mysql.com/" target="_blank"><img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" /></a>
  <a href="https://pusher.com/" target="_blank"><img src="https://img.shields.io/badge/Pusher-300847?style=for-the-badge&logo=pusher&logoColor=white" /></a>
  <a href="https://redis.io/" target="_blank"><img src="https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white" /></a>
  <a href="https://www.php.net/" target="_blank"><img src="https://img.shields.io/badge/PHP_8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" /></a>
</p>

---

## 🚀 Performance Engineering
This core engine is built for extreme efficiency and real-time reliability.

### ⚡ Aggressive Caching Strategy
Using **Laravel Cache-Aside**, we minimize database hits for frequently accessed data:
- **Global Settings**: Cached for 1 hour, auto-invalidated on update.
- **Sales Intelligence**: High-intensity analytics cached for 5 minutes.
- **Notification Counts**: Real-time unread counts cached for 60 seconds per user.

### 📊 SQL Intelligence
We moved heavy computations from PHP memory to the **SQL Database Layer**:
- **Aggregated Queries**: Using `DB::raw` for instant SUM/COUNT/GROUP BY across multi-vendor data.
- **Optimized Indexing**: Custom indexing on `created_at` and `site_id` ensures range queries execute in milliseconds.

---

## 🏗️ System Architecture
```mermaid
graph LR
    A[Clients] -->|REST API| B[Sanctum Auth]
    B --> C{Laravel Engine}
    C -->|Eloquent| D[(MySQL / Postgres)]
    C -->|Broadcast| E[Pusher / Echo]
    C -->|Cache| F[Redis / File]
    C -->|Mail| G[SMTP / Mailgun]
```

---

## 🔐 Advanced Security
- **Multi-Store Sanctum**: Secure token-based authentication with store-specific scopes.
- **Request Lifecycle**: Strict validation layers and CORS protection.
- **Data Integrity**: Foreign key constraints with cascading deletes across vendor scopes.

## 📡 Integrated With
- 🖥️ **[Nexus Admin Dashboard](https://github.com/salahuddingfx/Multi-Vendor-Admin)**
- 🛍️ **[Acharu Boutique](https://github.com/salahuddingfx/Acharu)**
- 🐟 **[TajaShutki Store](https://github.com/salahuddingfx/TajaShutki)**

---

<p align="center">
  <img src="https://img.shields.io/badge/Build-Stable-emerald?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Tests-Passing-blue?style=for-the-badge" />
</p>
