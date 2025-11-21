# 🚀 部署检查清单

## 部署前检查

### 1. 文件完整性 ✅
- [x] 所有 PHP 文件无语法错误
- [x] 所有 JavaScript 文件无语法错误
- [x] CSS 文件正确加载
- [x] 新增模块文件已创建

### 2. WordPress 设置
- [ ] 永久链接设置为 `/%postname%/`
- [ ] 评论分页已启用（20条/页）
- [ ] 嵌套评论已启用（5层）
- [ ] 缩略图尺寸设置为 800x500px

### 3. 服务器要求
- [ ] PHP 版本 >= 7.4
- [ ] WordPress 版本 >= 5.6
- [ ] 已安装 Zlib 扩展（用于 Gzip）
- [ ] 已启用 mod_rewrite（Apache）

---

## 部署步骤

### 步骤 1: 备份
```bash
# 备份数据库
mysqldump -u username -p database_name > backup.sql

# 备份主题文件
tar -czf theme-backup.tar.gz /path/to/wordpress/wp-content/themes/maupassant/
```

### 步骤 2: 上传文件
```bash
# 使用 FTP/SFTP 上传以下文件：
- functions.php
- header.php
- footer.php
- comments.php
- inc/*.php (所有新增文件)
- js/comment-enhancements.js
- css/comment.css
- css/back-to-top.css
```

### 步骤 3: 清除缓存
- [ ] 清除 WordPress 对象缓存
- [ ] 清除页面缓存插件缓存
- [ ] 清除 CDN 缓存（如使用）
- [ ] 清除浏览器缓存

### 步骤 4: 测试功能
- [ ] 首页加载正常
- [ ] 文章页面显示正常
- [ ] 评论提交功能正常
- [ ] AJAX 评论工作正常
- [ ] 回到顶部按钮工作
- [ ] 导航菜单正常
- [ ] 侧边栏显示正常

---

## 部署后验证

### 1. 性能测试
```bash
# 使用以下工具测试：
- Google PageSpeed Insights: https://pagespeed.web.dev/
- GTmetrix: https://gtmetrix.com/
- WebPageTest: https://www.webpagetest.org/

目标分数：
- Performance: > 90
- Accessibility: > 90
- Best Practices: > 90
- SEO: > 90
```

### 2. SEO 验证
- [ ] Meta description 正确显示
- [ ] Open Graph 标签存在
- [ ] Schema 标签验证通过（validator.schema.org）
- [ ] Canonical URL 正确
- [ ] Sitemap 可访问

### 3. 可访问性测试
```bash
# 使用以下工具：
- WAVE: https://wave.webaim.org/
- axe DevTools (浏览器扩展)
- Lighthouse (Chrome DevTools)

目标：WCAG 2.1 Level AA 合规
```

### 4. 安全检查
```bash
# 使用以下工具：
- Sucuri SiteCheck: https://sitecheck.sucuri.net/
- Security Headers: https://securityheaders.com/
- WPScan: https://wpscan.com/

目标：A+ 安全评级
```

### 5. 功能测试清单
- [ ] 评论提交（已登录用户）
- [ ] 评论提交（访客）
- [ ] 评论回复功能
- [ ] 评论分页
- [ ] 评论验证（邮箱、长度等）
- [ ] 垃圾评论防护
- [ ] 搜索功能
- [ ] 归档页面
- [ ] 分类页面
- [ ] 标签页面
- [ ] 404 页面

---

## 监控设置

### 1. 性能监控
```bash
# 设置以下监控：
- 页面加载时间
- 服务器响应时间
- 数据库查询时间
- 错误率
```

### 2. 安全监控
```bash
# 监控项目：
- 登录尝试
- 文件修改
- 可疑请求
- 安全日志
```

### 3. SEO 监控
```bash
# 使用工具：
- Google Search Console
- Bing Webmaster Tools
- 定期检查排名
```

---

## 故障排除

### 问题 1: 白屏/500 错误
```bash
# 解决方案：
1. 检查 PHP 错误日志
2. 禁用所有插件
3. 切换到默认主题测试
4. 检查 PHP 版本兼容性
5. 增加 PHP 内存限制
```

### 问题 2: 评论提交失败
```bash
# 解决方案：
1. 检查 JavaScript 控制台错误
2. 验证 AJAX URL 正确
3. 检查 nonce 验证
4. 清除缓存
5. 检查服务器 PHP 错误
```

### 问题 3: 样式显示异常
```bash
# 解决方案：
1. 清除浏览器缓存
2. 清除 WordPress 缓存
3. 检查 CSS 文件路径
4. 验证文件权限
5. 检查 CDN 设置
```

### 问题 4: 性能下降
```bash
# 解决方案：
1. 检查缓存是否启用
2. 优化数据库
3. 检查插件冲突
4. 验证服务器资源
5. 检查外部请求
```

---

## 回滚计划

### 如果出现严重问题：

```bash
# 步骤 1: 恢复主题文件
tar -xzf theme-backup.tar.gz -C /path/to/wordpress/wp-content/themes/

# 步骤 2: 恢复数据库（如需要）
mysql -u username -p database_name < backup.sql

# 步骤 3: 清除缓存
# 通过 WordPress 后台或命令行清除所有缓存

# 步骤 4: 验证网站正常
# 访问网站确认功能正常
```

---

## 优化建议

### 短期（1周内）
- [ ] 监控性能指标
- [ ] 收集用户反馈
- [ ] 修复发现的小问题
- [ ] 调整缓存策略

### 中期（1个月内）
- [ ] 分析 SEO 表现
- [ ] 优化热门页面
- [ ] 添加更多结构化数据
- [ ] 改进移动端体验

### 长期（3个月内）
- [ ] 实施 PWA 功能
- [ ] 添加 AMP 支持
- [ ] 优化图片格式（WebP）
- [ ] 实施 HTTP/3

---

## 维护计划

### 每日
- [ ] 检查错误日志
- [ ] 监控网站可用性

### 每周
- [ ] 审查安全日志
- [ ] 检查性能指标
- [ ] 备份数据库

### 每月
- [ ] 更新 WordPress 核心
- [ ] 更新插件
- [ ] 运行性能测试
- [ ] 审查 SEO 表现

### 每季度
- [ ] 完整安全审计
- [ ] 可访问性审计
- [ ] 代码审查
- [ ] 用户体验评估

---

## 联系信息

### 技术支持
- WordPress 官方: https://wordpress.org/support/
- 主题文档: 查看 OPTIMIZATIONS.md

### 紧急联系
- 服务器管理员: [填写联系方式]
- 开发团队: [填写联系方式]

---

## 签署确认

- [ ] 我已阅读并理解本检查清单
- [ ] 我已完成所有必要的备份
- [ ] 我已准备好回滚计划
- [ ] 我已通知相关团队成员

**部署人员**: _______________  
**日期**: _______________  
**签名**: _______________

---

**最后更新**: 2025年11月21日  
**版本**: 1.0  
**状态**: 准备部署
