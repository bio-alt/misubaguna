# robots.txt for PT Misuba Guna Indonesia (https://misubaguna.com)
# Allow search engines and modern AI / LLM search crawlers

User-agent: *
Allow: /
Disallow: /admin/
Disallow: /filament/
Disallow: /livewire/
Disallow: /storage/*.php

# OpenAI (ChatGPT & SearchGPT)
User-agent: GPTBot
Allow: /

User-agent: ChatGPT-User
Allow: /

# Anthropic (Claude)
User-agent: ClaudeBot
Allow: /

User-agent: anthropic-ai
Allow: /

# Perplexity AI
User-agent: PerplexityBot
Allow: /

# Google Gemini / AI Search
User-agent: Google-Extended
Allow: /

User-agent: GoogleOther
Allow: /

# Apple Intelligence
User-agent: Applebot-Extended
Allow: /

# Cohere
User-agent: cohere-ai
Allow: /

# Common Crawl (Foundation model dataset)
User-agent: CCBot
Allow: /

# Amazon AI
User-agent: Amazonbot
Allow: /

# Sitemaps and AI Context
Sitemap: https://misubaguna.com/sitemap.xml
# LLM Context: https://misubaguna.com/llms.txt
