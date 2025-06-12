# Post to Twitter

Automatically post your WordPress blog posts to your **Twitter account** when published, using the official Twitter API.

## ✨ Features

- 🐦 Auto-post newly published posts to your Twitter timeline
- ⚙️ Settings panel for Twitter API credentials
- ✅ Optional post-by-post toggle (via ACF `post_to_twitter` checkbox)
- 🔐 Uses OAuth 1.0a and [TwitterOAuth](https://github.com/abraham/twitteroauth)

---

## 📦 Installation

1. Upload the plugin folder to `/wp-content/plugins/post-to-twitter/`
2. Activate via **Plugins > Installed Plugins**
3. Go to **Settings > Post to Twitter** in wp-admin
4. Fill in your **Twitter API keys** and **access tokens** (see below)

---

## 🛠️ Twitter Developer App Setup

To post to Twitter, you need API keys and tokens from a Twitter Developer account.

### 🔧 Step 1: Create a Twitter Developer Account

1. Go to [https://developer.twitter.com/en/portal/dashboard](https://developer.twitter.com/en/portal/dashboard)
2. Sign in with your Twitter account
3. Create a new **Project & App**

---

### 🔑 Step 2: Generate API Keys and Access Tokens

In your Twitter Developer App:

1. Go to **Keys & Tokens**
2. Generate the following credentials:
   - `API Key`
   - `API Secret Key`
   - `Access Token`
   - `Access Token Secret`
3. Set **App Permissions** to `Read and Write`

---

### 💾 Step 3: Save Credentials in WordPress

1. In wp-admin, go to **Settings > Post to Twitter**
2. Paste:
   - `API Key`
   - `API Secret`
   - `Access Token`
   - `Access Token Secret`
3. Save settings

---

### ✅ Optional: ACF Checkbox for Per-Post Control

If you're using [Advanced Custom Fields](https://www.advancedcustomfields.com/), you can add a true/false field named:

```
post_to_twitter
```

This lets you control whether a post should be tweeted or not.

---

## 🔄 How It Works

- On **post publish**, plugin queues a background event using `wp_schedule_single_event`.
- It checks if the post has already been tweeted (`_post_tweeted` meta).
- Posts the title and link to your Twitter account using the `statuses/update` endpoint.

---

## 📝 Changelog

### 1.0
- Initial release — automatic Twitter posting with support for ACF toggle

---

## 🛡 License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
