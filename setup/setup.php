<?php

require_once __DIR__ . '/../autoload.php';

use App\Infrastructure\Database;

try {
    // Get setup configuration
    $config = require __DIR__ . '/../config/database.php';

    $hostValue = $config['host'];
    $portValue = $config['port'];
    $usernameValue = $config['username'];
    $passwordValue = $config['password'];
    $dbNameValue = $config['db_name'];

    // Connect to PostgreSQL server (without specific setup)
    $pdo = new PDO("pgsql:host={$hostValue};port={$portValue}", $usernameValue, $passwordValue);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to PostgreSQL server\n";

    // Check if setup exists
    $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
    $stmt->execute([$dbNameValue]);
    $exists = $stmt->fetch();

    if (!$exists) {
        // Create setup
        $pdo->exec("CREATE DATABASE $dbNameValue");
        echo "Database '$dbNameValue' created\n";
    } else {
        echo "Database '$dbNameValue' already exists\n";
    }

    // Connect to the setup using Database class
    $database = new Database();
    $pdo = $database->getConnection();

    echo "Connected to $dbNameValue setup\n";

    // Check if tables exist
    $stmt = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = '$dbNameValue'");
    $stmt->execute();
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        $sql = "
            -- Таблица постов - структура выровнена чтобы уменьшить место на диске
            CREATE TABLE posts (
                id          BIGINT GENERATED ALWAYS AS IDENTITY (CACHE 1) PRIMARY KEY,
                hotness     INTEGER       NOT NULL DEFAULT 0,
                view_count  INTEGER       NOT NULL DEFAULT 0,
                created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                title       VARCHAR(255)  NOT NULL,
                content     TEXT          NOT NULL
            );            
            
            -- Таблица просмотров
            CREATE TABLE user_post_views (
                id          BIGINT GENERATED ALWAYS AS IDENTITY (CACHE 1) PRIMARY KEY,
                user_id     INTEGER NOT NULL,
                post_id     INTEGER NOT NULL,
                viewed_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(user_id, post_id),
                -- внешний ключ только для демо - в проде не использую - доп. нагрузка на БД и пользы мало
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE 
            );
            
            -- Индексы на posts
            CREATE INDEX idx_posts_hotness ON posts(hotness DESC);
            CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
            CREATE INDEX idx_posts_view_count ON posts(view_count);
            -- Частичный индекс по view_count < 1000
            CREATE INDEX ON posts(view_count) WHERE view_count < 1000;

            -- Индексы для user_post_views
            CREATE INDEX idx_user_post_views_user_id ON user_post_views(user_id);
            CREATE INDEX idx_user_post_views_post_id ON user_post_views(post_id);
            CREATE UNIQUE INDEX idx_user_post_views_user_post ON user_post_views(user_id, post_id);
            
        ";

        $pdo->exec($sql);
        echo "Tables created\n";

        // Insert sample data
        $samplePosts = [
            ['Первый пост о технологиях', 'Содержимое первого поста о последних технологических трендах',],
            ['Новости науки', 'Интересные открытия в области физики и химии',],
            ['Спортивные события', 'Обзор последних спортивных соревнований',],
            ['Кулинарные рецепты', 'Простые и вкусные рецепты для дома',],
            ['Путешествия по миру', 'Лучшие места для отдыха в 2024 году',],
            ['Финансовые советы', 'Как правильно инвестировать деньги',],
            ['Здоровый образ жизни', 'Советы по поддержанию здоровья',],
            ['Искусство и культура', 'Обзор современного искусства',],
            ['Автомобильные новости', 'Новые модели автомобилей 2024',],
            ['Образование и карьера', 'Советы по развитию карьеры',]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO posts (title, content, hotness, view_count) 
            VALUES (:title, :content, :hotness, :view_count)
        ");

        foreach ($samplePosts as $post) {
            $stmt->execute([
                ':title' => $post[0],
                ':content' => $post[1],
                ':hotness' => mt_rand(0, 500),
                ':view_count' => mt_rand(0, 1200),
            ]);
        }

        echo "Sample posts inserted\n";

        // Generate more test data with varied content
        $titleTemplates = [
            'Новости технологий',
            'Обзор игр',
            'Спортивные события',
            'Кулинарные рецепты',
            'Путешествия',
            'Финансовые советы',
            'Здоровье и фитнес',
            'Искусство и культура',
            'Автомобили',
            'Образование',
            'Мода и стиль',
            'Музыка и концерты',
            'Фильмы и сериалы',
            'Книги и литература',
            'Наука и исследования',
            'Экология и природа',
            'Политика и общество',
            'Бизнес и стартапы',
            'Психология',
            'История'
        ];

        $contentTemplates = [
            'Подробный анализ последних тенденций и их влияние на современное общество.',
            'Экспертное мнение и рекомендации от ведущих специалистов в данной области.',
            'Интересные факты и статистика, которые могут вас удивить.',
            'Практические советы и рекомендации для повседневной жизни.',
            'Обзор новинок и трендов, которые стоит знать каждому.',
            'Глубокое исследование темы с примерами из реальной жизни.',
            'Сравнительный анализ различных подходов и методов.',
            'История развития и современное состояние вопроса.',
            'Пошаговое руководство для начинающих и профессионалов.',
            'Мнения экспертов и прогнозы на будущее развитие.'
        ];

        for ($i = 11; $i <= 5000; $i++) {
            $titleTemplate = $titleTemplates[array_rand($titleTemplates)];
            $contentTemplate = $contentTemplates[array_rand($contentTemplates)];

            $stmt->execute([
                ':title' => $titleTemplate . ' - выпуск ' . $i,
                ':content' => $contentTemplate . ' Материал номер ' . $i . ' содержит актуальную информацию и полезные данные.',
                ':hotness' => mt_rand(0, 800),
                ':view_count' => mt_rand(0, 1200)
            ]);
        }

        echo "Generated 5000 test posts\n";

        // Insert some user views for testing
        $userViews = [
            [1, 1], [1, 2], [1, 3],
            [2, 1], [2, 4], [2, 5],
            [3, 2], [3, 6], [3, 7], [3, 8]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO user_post_views (user_id, post_id) 
            VALUES (:user_id, :post_id)
        ");

        foreach ($userViews as $view) {
            $stmt->execute([
                ':user_id' => $view[0],
                ':post_id' => $view[1]
            ]);
        }

        echo "Sample user views inserted\n";

    } else {
        echo "Tables already exist\n";
    }

    // Show statistics
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $postsCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM user_post_views");
    $viewsCount = $stmt->fetchColumn();

    echo "\nDatabase setup completed!\n";
    echo "Total posts: {$postsCount}\n";
    echo "Total user views: {$viewsCount}\n";
    echo "\nYou can now test the API at: http://localhost/api/posts?user_id=1\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}