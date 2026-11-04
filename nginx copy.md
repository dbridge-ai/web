server
{
    listen 80;
    listen 443 ssl;
    listen 443 quic;
    http2 on;
    http3 on;
    server_name www.dbridge-ai.com www.dbridge-ai.cn;
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/www.dbridge-ai.com;
    #CERT-APPLY-CHECK--START
    # 用于SSL证书申请时的文件验证相关配置 -- 请勿删除
    include /www/server/panel/vhost/nginx/well-known/www.dbridge-ai.com.conf;
    #CERT-APPLY-CHECK--END
    include /www/server/panel/vhost/nginx/extension/www.dbridge-ai.com/*.conf;
    
    #SSL-START SSL相关配置，请勿删除或修改下一行带注释的404规则
    #error_page 404404.html;
    #HTTP_TO_HTTPS_START
    set $isRedcert 1;
    if ($server_port != 443) {
        set $isRedcert 2;
    }
    if ( $uri ~ /\\.well-known/ ) {
        set $isRedcert 1;
    }
    if ($isRedcert != 1) {
        rewrite ^(/.*)$ https://$host$1 permanent;
    }
    #HTTP_TO_HTTPS_END
    ssl_certificate    /www/server/panel/vhost/cert/www.dbridge-ai.com/fullchain.pem;
    ssl_certificate_key    /www/server/panel/vhost/cert/www.dbridge-ai.com/privkey.pem;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_tickets on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    add_header Strict-Transport-Security "max-age=31536000";
    add_header Alt-Svc 'quic=":443"; h3=":443"; h3-29=":443"; h3-27=":443";h3-25=":443"; h3-T050=":443"; h3-Q050=":443";h3-Q049=":443";h3-Q048=":443"; h3-Q046=":443"; h3-Q043=":443"';
    quic_retry on;
    quic_gso on;
    ssl_early_data on;
    error_page 497  https://$host$request_uri;

    #SSL-END

    #ERROR-PAGE-START  错误页配置，可以注释、删除或修改
    error_page 404 404.html;
    #error_page 502 502.html;
    #ERROR-PAGE-END

    #PHP-INFO-START  PHP引用配置，可以注释或修改
    include enable-php-00.conf;
    #PHP-INFO-END

    #REWRITE-START URL重写规则引用,修改后将导致面板设置的伪静态规则失效
    include /www/server/panel/vhost/rewrite/www.dbridge-ai.com.conf;
    #REWRITE-END

    # === 移动端重定向配置（新增） ===
    # 先重置为0
    set $is_mobile 0;

    # 排除静态资源，不让静态文件走移动端重写
    if ($uri ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$) {
        set $is_mobile 0;
    }

    # 检测移动端 User-Agent
    if ($http_user_agent ~* "android|iphone|ipod|opera mini|opera mobile|mobile|silk|blackberry|windows phone|iemobile|up.browser|up.link|wap|pda|kindle|miui|xiaomi|huawei|oppo|vivo|oneplus|realme|asus|lenovo|meizu") {
        set $is_mobile 1;
    }

    # 移动端重写规则
    if ($is_mobile = 1) {
        # 根路径直接指向 h5index.html
        rewrite ^/$ index.html last;
        # 带 .html 后缀的页面，前缀加上 /h5/
        rewrite ^(.*)\.html$ $1.html last;
        # 无后缀的页面，自动补 .html 并加上 /h5/ 前缀
        rewrite ^/(.*)$ $1.html last;
    }
    # === 移动端重定向配置结束 ===

    # 禁止访问的敏感文件
    location ~* (\\.user.ini|\\.htaccess|\\.htpasswd|\\.env.*|\\.project|\\.bashrc|\\.bash_profile|\\.bash_logout|\\.DS_Store|\\.gitignore|\\.gitattributes|LICENSE|README\\.md|CLAUDE\\.md|CHANGELOG\\.md|CHANGELOG|CONTRIBUTING\\.md|TODO\\.md|FAQ\\.md|composer\\.json|composer\\.lock|package(-lock)?\\.json|yarn\\.lock|pnpm-lock\\.yaml|\\.\\w+~|\\.swp|\\.swo|\\.bak(up)?|\\.old|\\.tmp|\\.temp|\\.log|\\.sql(\\.gz)?|docker-compose\\.yml|docker\\.env|Dockerfile|\\.csproj|\\.sln|Cargo\\.toml|Cargo\\.lock|go\\.mod|go\\.sum|phpunit\\.xml|phpunit\\.xml|pom\\.xml|build\\.gradl|pyproject\\.toml|requirements\\.txt|application(-\\w+)?\\.(ya?ml|properties))$
    {
        return 404;
    }
    
    # 禁止访问的敏感目录
    location ~* /(\.git|\.svn|\.bzr|\.vscode|\.claude|\.idea|\.ssh|\.github|\.npm|\.yarn|\.pnpm|\.cache|\.husky|\.turbo|\.next|\.nuxt|node_modules|runtime)/ {
        return 404;
    }

    #一键申请SSL证书验证目录相关设置
    location ~ \\.well-known{
        allow all;
    }

    #禁止在证书验证目录放入敏感文件
    if ( $uri ~ "^/\\.well-known/.*\\.(php|jsp|py|js|css|lua|ts|go|zip|tar\\.gz|rar|7z|sql|bak)$" ) {
        return 403;
    }

    location ~ .*\\.(gif|jpg|jpeg|png|bmp|swf)$
    {
        expires      30d;
        error_log /dev/null;
        access_log /dev/null;
    }

    location ~ .*\\.(js|css)?$
    {
        expires      12h;
        error_log /dev/null;
        access_log /dev/null;
    }

    access_log  /www/wwwlogs/www.dbridge-ai.com.log;
    error_log  /www/wwwlogs/www.dbridge-ai.com.error.log;
}