# ==========================================================================
# 最適化されたWordPress テーマ用 Docker ビルド
# ==========================================================================

# 軽量なベースイメージを使用
FROM wordpress:6.4-php8.2-apache as base

# システム環境変数を設定（ビルド最適化）
ENV DEBIAN_FRONTEND=noninteractive
ENV APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1

# 必要最小限のパッケージのみインストール
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        zip \
        intl \
        gd \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Apache最適化設定
RUN a2enmod rewrite headers expires deflate \
    && echo "ServerTokens Prod" >> /etc/apache2/apache2.conf \
    && echo "ServerSignature Off" >> /etc/apache2/apache2.conf

# Apache設定ファイルをコピー
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# PHP設定最適化
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
    echo 'max_execution_time=30'; \
    echo 'memory_limit=128M'; \
    echo 'upload_max_filesize=32M'; \
    echo 'post_max_size=32M'; \
} > /usr/local/etc/php/conf.d/optimization.ini

# --------------------------------------
# 開発環境用ステージ
# --------------------------------------
FROM base as development

# 開発用PHP設定を追加
RUN { \
    echo 'display_errors=On'; \
    echo 'error_reporting=E_ALL'; \
    echo 'log_errors=On'; \
    echo 'error_log=/var/log/apache2/php_error.log'; \
    echo 'WORDPRESS_DEBUG=1'; \
    echo 'WORDPRESS_DEBUG_LOG=1'; \
    echo 'WORDPRESS_DEBUG_DISPLAY=1'; \
} > /usr/local/etc/php/conf.d/development.ini

# WP-CLIのインストール（開発用ツール）
RUN curl -o /tmp/wp-cli.phar https://raw.githubusercontent.com/wp-cli/wp-cli/v2.8.1/wp-cli.phar \
    && php /tmp/wp-cli.phar --info \
    && chmod +x /tmp/wp-cli.phar \
    && mv /tmp/wp-cli.phar /usr/local/bin/wp \
    && rm -rf /tmp/*

# 作業ディレクトリ設定
WORKDIR /var/www/html

# WordPress設定のコピー（開発用設定を使用）
COPY wp-config.docker.php ./wp-config.php

# テーマファイルのコピー（開発用）
COPY themes/kei-portfolio/ ./wp-content/themes/kei-portfolio/

# 権限設定
RUN chown -R www-data:www-data /var/www/html/wp-content \
    && find /var/www/html/wp-content -type d -exec chmod 755 {} \; \
    && find /var/www/html/wp-content -type f -exec chmod 644 {} \;

# ヘルスチェック設定
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/wp-admin/install.php || exit 1

EXPOSE 80

# --------------------------------------
# 本番環境用ステージ（今回は開発環境のみ使用）
# --------------------------------------
FROM base as production

# 本番用PHP設定
RUN { \
    echo 'display_errors=Off'; \
    echo 'error_reporting=E_ERROR | E_WARNING | E_PARSE'; \
    echo 'log_errors=On'; \
    echo 'error_log=/var/log/apache2/php_error.log'; \
} > /usr/local/etc/php/conf.d/production.ini

# 作業ディレクトリ設定
WORKDIR /var/www/html

# WordPress設定のコピー（開発用設定を使用）
COPY wp-config.docker.php ./wp-config.php

# テーマファイルのコピー
COPY themes/kei-portfolio/ ./wp-content/themes/kei-portfolio/

# 本番環境用の権限設定（より厳格）
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod 600 /var/www/html/wp-config.php

# ヘルスチェック設定
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

EXPOSE 80

# デフォルトステージは開発環境
FROM development