# 更新日志

所有对 Lared 主题的显著更改都将记录在此文件中。

格式基于 [Keep a Changelog](https://keepachangelog.com/zh-CN/1.0.0/)，
并且本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

---

## [1.0.2] - 2026-02-24

### ✨ 新增
- 🆕 下载按钮短代码 `[download_button]` PHP 实现（`inc/inc-download-button.php`）
- 🆕 文章内链接自动添加 link icon（SVG 外链箭头图标）
- 🆕 评论回复链接安全拦截（事件委托，防止 PJAX 导航刷新）
- 🆕 评论 60 秒编辑倒计时机制（AJAX 提交后可修改评论）
- 🆕 评论表情系统（Emoji 面板 + 自定义表情渲染）
- 🆕 评论表头标题栏（头像 / 登录信息 / 回头访客欢迎语）
- 🆕 回头访客自动识别（Cookie 记忆 + 邮箱头像实时检测）
- 🆕 管理员评论徽章（皇冠图标 + "博主"提示）
- 🆕 评论时间精确到秒
- 🆕 评论回复按钮改为图标（`fa-reply`）
- 🆕 取消回复改为红色 X 图标，标题动态显示"回复 昵称"

### 🐛 修复
- 🔧 修复文章链接 icon class 名不匹配（`external-link-icon` → `lared-inline-link-icon`）
- 🔧 修复 PJAX 拦截评论回复链接导致页面刷新
- 🔧 修复 PJAX 切换后 `addComment.init()` 未重新初始化
- 🔧 修复评论作者名红色被 Tailwind 覆盖（添加 `!important`）
- 🔧 修复 `.comment-list .fn` 颜色冲突规则
- 🔧 修复编辑倒计时中点击回复导致页面刷新
- 🔧 修复 Cookie 保存（强制 `setcookie` 在 AJAX 响应中设置）
- 🔧 修复 CSS/JS 版本号缓存（`filemtime()` 替代静态 `1.0.0`）

### 🎨 改进
- 💅 下载按钮排除 link icon（`no-arrow` + `dl-button` 双重检测）
- 💅 提交按钮 loading 动画增强（加粗边框 + 加大尺寸 + 加快转速）
- 💅 Toast 提示改为直角、小字体（13px），移除所有 emoji 符号
- 💅 管理员徽章 tooltip 从"站长"改为"博主"
- 💅 子评论 `.comment-content` 样式独立
- 💅 取消编辑按钮样式优化

---

## [1.0.0] - 2026-02-23

### 🎉 初始发布

#### 新增
- ✨ 主题基础框架，支持 WordPress 6.0+
- ✨ 响应式设计，适配移动端和桌面端
- ✨ PJAX 无刷新页面加载
- ✨ APlayer 音乐播放器集成
  - 支持自定义播放列表（JSON 格式）
  - 支持网易云/QQ音乐歌单
  - Meting API 解析
- ✨ Memos 动态集成
  - API 数据获取
  - 本地缓存支持
  - 关键词提取
- ✨ RSS 订阅聚合
  - 友链 RSS 聚合
  - 文件缓存机制
  - 自动刷新缓存
- ✨ 代码高亮功能
  - PrismJS 集成
  - 自动语言检测
  - 行号显示
  - 代码复制按钮
  - 长代码折叠
- ✨ 图片灯箱查看
  - ViewImage 集成（轻量级图片灯箱）
  - 支持文章内图片点击查看
  - 支持 `no-view` 属性排除特定图片
- ✨ 图片懒加载
  - 原生懒加载 API
  - 后台设置开关
  - 自动处理文章和特色图片
- ✨ 图片加载动画
  - 淡入效果（Fade In）
  - 像素化显现（Pixelate）
  - 后台可视化选择
- ✨ 文章图片 Loading 占位
  - 提前占位避免布局跳动
  - Tailwind CSS 风格旋转圆圈
  - 自适应图片比例
- ✨ 下载按钮短代码
  - [download_button] 短代码支持
  - 文件信息展示（格式、版本、大小）
  - 美观的下载卡片样式
- ✨ 内联代码样式
  - 普通代码：淡红底 + 深红字
  - 高亮代码：红底白字（用于 UUID/密钥）
  - [red_code] 短代码支持
  - pre 代码块保持原样
- ✨ 代码运行器
  - [code_runner] 短代码
  - HTML/CSS/JS 实时预览
  - 代码高亮展示
- ✨ AJAX 评论提交
  - 无刷新提交
  - 实时评论统计
  - 评论者计数
- ✨ 文章目录导航
  - 自动生成目录
  - 滚动监听高亮
  - 平滑滚动
- ✨ 首页特色功能
  - Hero 区域（最新/热门/热评/随机文章）
  - 60天更新热力图
  - 热门文章侧边栏
  - 最新评论展示
  - 标签云
  - 统计信息
- ✨ 主题设置面板
  - 音乐播放器配置
  - Memos API 配置
  - Umami 统计代码
  - 十年博客起始日期
  - RSS 缓存管理
- ✨ Font Awesome 图标支持
- ✨ 自定义字体（Noto Sans SC, PaperMono）

#### 技术特性
- 🚀 PHP 8.0+ 类型提示
- 🚀 Tailwind CSS v4.1.6
- 🚀 模块化代码结构
- 🚀 完善的输入过滤和输出转义
- 🚀 Transient 缓存机制
- 🚀 国际化支持（i18n）

---

## 版本说明

### 版本号格式

版本号格式：`主版本号.次版本号.修订号`

- **主版本号**：重大更新，可能包含破坏性变更
- **次版本号**：新增功能，向下兼容
- **修订号**：问题修复，向下兼容

### 版本标签说明

- 🎉 **重大更新** - 里程碑版本
- ✨ **新增** - 新功能
- 🐛 **修复** - 问题修复
- 🚀 **优化** - 性能优化
- 🔒 **安全** - 安全更新
- 📝 **文档** - 文档更新
- ♻️ **重构** - 代码重构

---

## 升级指南

### 从 v1.0.0 升级到 v1.x.x

1. 备份当前主题文件和数据库
2. 下载新版本主题
3. 停用当前主题
4. 删除旧版本主题文件
5. 上传新版本主题
6. 启用主题
7. 检查主题设置是否需要重新配置

### 数据库变更

v1.0.0 使用的选项：
- `lared_aplayer_playlist_json`
- `lared_music_playlist_urls`
- `lared_music_meting_api_template`
- `lared_memos_site_url`
- `lared_memos_api_url`
- `lared_memos_api_token`
- `lared_memos_page_size`
- `lared_umami_script`
- `lared_ten_year_start_date`
- `lared_template_path_migrated_v4`

---

## 兼容性

| 版本 | WordPress | PHP |
|------|-----------|-----|
| 1.0.0 | 6.0+ | 8.0+ |

---

## 贡献者

- 西风 - 初始开发和设计

---

**注意**: 更新日志从 v1.0.0 开始记录，之前的开发版本变更未包含在此文件中。
