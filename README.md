

### 伪静态页配置：


##### 默认使用 Apache 配置
将以下内容存为 .htaccess, 放在网站根目录下

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php [L]

##### 禁止Apache 列出目录结构
    <Files *>
    Options -Indexes
    </Files>


##### Nginx 伪静态页配置

将以下内容拷贝到 nginx 的配置文件中

    location / {
        if (!-e $request_filename){
            rewrite ^/(.*)$ /index.php last;
        }
    }


##### IIS 伪静态页配置

将以下内容存为 web.config， 放在网站根目录下

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


### 目录权限

##### cache 目录
存放系统生成的临时文件，需要可读可写权限。

##### data 目录
存放APP应用上传的内容，需要可读可写权限，自动化运维配置时可用软链接。
