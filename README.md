Welcome to the interview test.

Please set up the database credentials by coping the .env.example to .env and change the below environment files accordingly

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=interview_solution
DB_USERNAME=root
DB_PASSWORD=
```

Then run this command

`php artisan migrate --seed`

Admin or the third party credentials.

```
email: admin@admin.com
password: password
```
After seeding, the user with the above credential will be able to manage other users customers.
