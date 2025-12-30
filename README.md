
### Шаги развертывания

1. **Клонирование репозитория**

    ```bash
    git clone https://github.com/Skiqa/game.git
    ```

2. **Переход в рабочую директорию**

    ```bash
    cd game
    ```

3. **Подготовка окружения**

    ```bash
    cp .env.example .env
    cp .env.example .env.testing 
    ```

4. **Запуск локального окружения**

    ```bash
    docker compose up -d 
    ```

5. **Запуск контейнера fpm (все последующие команды выполняем из него)**

    ```bash
    docker exec -it igaming-fpm bash
    ```

6. **Установка зависимостей**

    ```bash
    composer install
    ```

7. **Генерация ключа приложения**

    ```bash
    php artisan key:generate
    ```

8. **Применение миграций и заполнение базы данных начальными данными**

    ```bash
    php artisan migrate --seed
    ```

9. **В конце можно протестировать проект**

    ```bash
    php artisan test --parallel --env=testing
    ```

С этого момента проект будет доступен по адресу http://localhost:7890
