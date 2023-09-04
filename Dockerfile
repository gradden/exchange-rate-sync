FROM webdevops/php-nginx:8.2
ARG version
ENV WEB_DOCUMENT_ROOT="/app/public"

RUN wget -O "/usr/local/bin/go-replace" "https://github.com/webdevops/goreplace/releases/download/1.1.2/gr-arm64-linux" \
    && chmod +x "/usr/local/bin/go-replace" \
    && "/usr/local/bin/go-replace" --version

RUN crontab -u application /app/cron
RUN chown -R application:application /app
EXPOSE 80
