### 1. Clone & Install
```bash
git clone [https://github.com/SASwagoto/uu-bus-manage.git](https://github.com/SASwagoto/uu-bus-manage.git)
cd uu-bus-manage
composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

php artisan migrate --seed
php artisan storage:link

Admin Access
URL: http://127.0.0.1:8000/admin/login

Default Login: admin@gmail.com | Password: 12345678


### 🌐 API Endpoints (Documentation)

All API responses are in `JSON` format. Protected routes require `Authorization: Bearer {token}` header.

#### 🔑 Authentication
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/register` | Create a new user account |
| `POST` | `/api/login` | Login & get Sanctum Token |
| `GET` | `/api/user` | Get authenticated user info |
| `POST` | `/api/logout` | Revoke token and logout |

#### 🚛 Driver Side (Protected)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/driver/trip/start` | Start a new trip |
| `POST` | `/api/driver/trip/{id}/update-location` | Update live Lat/Lng |
| `POST` | `/api/driver/trip/{id}/end` | End the current trip |

#### 🎓 Passenger Side (Protected)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/passenger/active-trips` | List all buses currently on way |
| `GET` | `/api/passenger/trip/{id}/track` | Get live location of a specific bus |
| `POST` | `/api/passenger/trip/{id}/check-in` | Increment passenger count |

#### 📅 General (Protected)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/schedules` | View today's bus schedule list |

---
**Base URL:** `http://your-ip-or-domain/api`