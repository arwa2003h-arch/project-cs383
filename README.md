# Fresh Laundry — CS383 Project

Laundry Service Management System built with Laravel as **microservices**.
Each service is its own Laravel project with its own MySQL database.

| Folder | Database | Port | Owner |
|---|---|---|---|
| `auth-service/` | `auth_db` | 8000 | Norah |
| `order-service/` | `orders_db` | 8001 | Arwa (services) + Mariah (orders) |
| `notification-service/` | `notifications_db` | 8002 | Arwa |
| `dashboard-service/` | — | 8003 | Layan |
| `website/` | — | 8080 | Layan + everyone adds their pages |

---

## أول مرة (كل بنت تسويها مرة وحدة)

```
cd C:\laragon\www
git clone https://github.com/arwa2003h-arch/project-cs383.git
```

وبعدها لكل service تحتاجينه (مثال order-service):

```
cd C:\laragon\www\project-cs383\order-service
composer install
copy .env.example .env
php artisan key:generate
```

افتحي `.env` وعدلي:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=orders_db
DB_USERNAME=root
DB_PASSWORD=
```

وبعدها:

```
php artisan migrate
php artisan db:seed
php artisan serve --port=8001
```

---

## كل يوم

**قبل ما تبدئين:**
```
git pull
```

**بعد ما تخلصين شيء:**
```
git add .
git commit -m "وش سويتي"
git pull
git push
```

---

## قواعد الفريق

1. كل وحدة تشتغل في المجلد حقها بس.
2. في `order-service/routes/api.php`: أروى تكتب تحت `Laundry services (Arwa)` وماريا تكتب تحت `Orders (Mariah)`.
3. ارفعي شغلك على دفعات صغيرة، وكل commit يكون واضح (يبين شغل كل وحدة للدكتورة).
4. لا ترفعين ملف `.env` ولا مجلد `vendor` (Laravel يمنعهم تلقائياً بـ `.gitignore`).
5. كل وحدة تكتب شغلها في الـ Development Journal.

---

## كيف الخدمات تكلم بعض

- كل service يتحقق من الـ JWT بنفسه (secret مشترك في `.env`).
- لا يوجد service يقرا جداول service ثاني، كل شيء عن طريق API.
- لما ماريا تغيّر حالة طلب، ترسل:

```php
Http::withToken($token)->post('http://127.0.0.1:8002/api/notifications', [
    'user_id'  => $order->user_id,
    'order_id' => $order->id,
    'status'   => $order->status,   // Pending, In Progress, Ready for Pickup, Completed, Cancelled
]);
```
