# RCVN - Training PHP Project
## _Dự án Training test về PHP tại RCVN_

Project thực hiện CRUD căn bản cho Dashboard của User
Với Back-end xử lý bằng PHP - Laravel Framework, Front-end được xử lý bằng Vuejs Framework

## Tính năng chính

- Xử lý đăng nhập, đăng xuất và xác thực của User, Khách hàng
- Dashboard hiển thị danh sách user, khách hàng, sản phẩm
- Thêm mới, chỉnh sửa, cập nhật, khóa tài khoản, xử lý soft-delete user
- Thêm mới, chỉnh sửa, cập nhật Khách hàng
- Thêm mới, chỉnh sửa, cập nhật, xóa sản phẩm
- Xử lý hình ảnh của sản phẩm
- Validation form Login, form tạo mới, form cập nhật chỉnh sửa
- Search toàn trang

## Tech

Hệ thống base trên các công nghệ:

- [PHP 8.1.6](https://www.php.net/)
- [Laravel 9](https://laravel.com/)
- [Javascript ES6](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- [VueJs 2](https://vuejs.org/).

## Cài đặt và khởi chạy dự án

Trước khi khởi chạy dự án, đảm bảo máy tính đã được cài đặt những thành phần sau:
- [Composer](https://getcomposer.org/) - Hướng dẫn cài đặt Composer cho Linux/ Windows tại trang này
- [Git](https://git-scm.com/downloads) - Hướng dẫn cài đặt môi trường Git
- [PHP 8.1.6](https://www.php.net/) - Có thể cài đặt theo gói XAMPP mới nhất
- Cài đặt [MySQL](https://www.mysql.com/downloads/) hoặc [PostgresSQL](https://www.postgresql.org/download/) (Nếu sử dụng XAMPP thì có thể dùng MySQL và PHP theo gói đã cài đặt)
- [Node.js và npm](https://nodejs.org/en/download/) - Cài đặt Nodejs, npm

Clone dự án về máy cá nhân

```sh
git clone https://github.com/vqh-quanghuy/rcvn-php-training.git
```

Di chuyển vào thư mục dự án để thao tác

```sh
cd rcvn-php-training
```

**Backend Setup**
Di chuyển vào thư mục Backend để setup

```sh
cd be-rcvn-training
```

Cài đặt các package sử dụng cho Backend

```sh
composer install
```

Tạo key cho dự án bằng lệnh
```sh
php artisan key:generate
```

Kết nối Backend với DB trên máy
Sử dụng pgAdmin hoặc phpmyadmin để tạo mới Database rỗng **"rcvn_training"**

```sh
cd be-rcvn-training
cp .env.example .env
```
Dùng Nano hoặc Vim hoặc text editor bất kỳ để cập nhật thông tin môi trường

```sh
nano .env
```

Thay đổi nội dung tại các dòng sau

```sh
APP_URL=http://localhost:8000 {Port tránh xung đột}

DB_CONNECTION={mysql/pgsql đang sử dụng}
DB_HOST={host chạy CSDL}
DB_PORT={port kết nối CSDL được thiết lập trên máy}
DB_DATABASE=rcvn_training
DB_USERNAME={Username để kết nối vào CSDL}
DB_PASSWORD={Mật khẩu để kết nối}   
```

Lưu file .env được thay đổi ở trên
Chạy khởi tạo table
```sh
php artisan migrate
```

Tạo dữ liệu ban đầu bằng seeder có sẵn
```sh
php artisan db:seed
```

Khởi chạy hệ thống
```sh
php artisan serve
```

Server đã chạy bình thường và expose trên http://127.0.0.1:8000/
Ta có thể dùng postman để test với các API của hệ thống

Tài khoản user mặc định được tạo bằng seeder là
- Username: huy_user01@mail.com
- Password: password

**Frontend Setup**
Tạo 1 terminal khác và di chuyển qua thư mục Front-end
```sh
cd fe-rcvn-training
```

Cài đặt npm
```sh
npm install
```

Trường hợp backend sử dụng port khác với port {8000}, cần dùng nano để điều chỉnh Url API của source back-end tại file src/main.js

```javascript
Vue.prototype.$backendUrl = 'http://127.0.0.1:{port}/api/';
Vue.prototype.$backendImageUrl = 'http://127.0.0.1:{port}/images/';
```

Chạy dự án
```sh
npm run serve
```

Chương trình được expose mặc định tại http://localhost:8080/
Truy cập vào hệ thống với Username và Password mặc định để sử dụn.

All done!
