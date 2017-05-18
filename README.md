

伪静态页配置：

默认使用 Apache 栩置
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]

Nginx 伪静态页配置
location / {
    if (!-e $request_filename){
        rewrite ^/(.*)$ /index.php last;
    }
}


IIS 伪静态页配置
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
  <system.webServer>
    <rewrite>
      <rules>
			<rule name="Main Rule" stopProcessing="true">
				<match url=".*" />
				<conditions logicalGrouping="MatchAll" trackAllCaptures="false">
					<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
					<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
				</conditions>
				<action type="Rewrite" url="index.php/{R:0}" />
			</rule>
			<rule name="BE" patternSyntax="Wildcard">
				<match url="*" />
				<conditions logicalGrouping="MatchAll" trackAllCaptures="false">
					<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
					<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
				</conditions>
				<action type="Rewrite" url="index.php" />
			</rule>
		</rules>
    </rewrite>
  </system.webServer>
</configuration>