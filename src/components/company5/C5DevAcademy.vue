<template>
  <div :class="['c5-page-root', { dark: isDarkMode, rtl: isArabic }]">
    <!-- Top Navigation Bar -->
    <header class="c5-navbar">
      <div class="nav-left">
        <router-link to="/dashboard" class="brand-link">
          <img src="@/assets/Gittax/logo1.png" alt="Brand Logo" class="brand-logo" />
          <span class="brand-name">Fawaz Platform</span>
        </router-link>
        <span class="nav-divider">/</span>
        <span class="nav-c5-badge">🎓 Company 5: Developer Academy & Free Courses</span>
      </div>

      <div class="nav-right">
        <router-link to="/dashboard" class="nav-pill-btn">🏠 Hub</router-link>
        <router-link to="/c2/home" class="nav-pill-btn">🏢 Company 2</router-link>
        <router-link to="/c3/stocks" class="nav-pill-btn">📈 Company 3</router-link>
        <router-link to="/c4/properties" class="nav-pill-btn">💎 Company 4</router-link>
        <router-link to="/invoices" class="nav-pill-btn">📑 Invoices</router-link>
        <router-link to="/settings" class="nav-pill-btn">⚙️ Settings</router-link>

        <button class="nav-icon-btn" @click="toggleDarkMode" :title="'Toggle Dark Mode'">
          {{ isDarkMode ? '☀️' : '🌙' }}
        </button>
        <button class="nav-icon-btn" @click="toggleLanguage" :title="'Switch Language'">
          🌐 {{ isArabic ? 'EN' : 'عربي' }}
        </button>

        <div class="nav-user-pill">
          <img :src="userAvatar || defaultAvatar" alt="Avatar" class="user-avatar-sm" />
          <span class="user-name-sm">{{ displayName }}</span>
        </div>

        <button class="nav-logout-btn" @click="handleLogout" title="Logout">
          🚪 Logout
        </button>
      </div>
    </header>

    <!-- Top Live Academy Ticker -->
    <div class="academy-ticker">
      <div class="ticker-track">
        <span>🚀 <strong>100% Free Open-Source Education:</strong> Zero Tuition, Full Access</span>
        <span class="t-sep">•</span>
        <span>📚 <strong>140+ Free Developer Courses</strong> Available</span>
        <span class="t-sep">•</span>
        <span>🤖 <strong>Trending:</strong> AI Agents, PyTorch, Vue 3 & FullStack Laravel</span>
        <span class="t-sep">•</span>
        <span>📜 <strong>Free Certificates:</strong> Harvard CS50, freeCodeCamp, FullStackOpen</span>
        <span class="t-sep">•</span>
        <span>⚡ <strong>Roadmaps Included:</strong> Frontend, Backend, DevOps & Cybersecurity</span>
      </div>
    </div>

    <!-- Main Container -->
    <main class="c5-main-container">
      <!-- Hero Banner -->
      <div class="c5-hero-banner">
        <div class="hero-left">
          <div class="badge-row">
            <span class="badge-c5">🎓 Company 5 Education Hub</span>
            <span class="badge-free">🎁 100% Free Certified Online Courses</span>
          </div>
          <h1 class="c5-title">Developer Academy & Open Learning Hub</h1>
          <p class="c5-subtitle">
            Master software engineering, full-stack web development, generative AI, cloud architecture, and cybersecurity with curated free courses, interactive playgrounds, and official documentation resources.
          </p>
        </div>

        <div class="hero-right-actions">
          <button class="c5-primary-btn" @click="openRoadmapModal">
            🗺️ View Career Roadmaps
          </button>
          <button class="c5-secondary-btn" @click="openCodePlayground">
            💻 Live Code Playground
          </button>
        </div>
      </div>

      <!-- KPI Summary Cards -->
      <div class="c5-kpi-grid">
        <div class="c5-kpi-card" @click="selectedTopic = 'All Topics'" style="cursor:pointer">
          <div class="kpi-icon-wrap indigo">🎓</div>
          <div class="kpi-meta">
            <span class="kpi-val">{{ courses.length }} Free Courses</span>
            <span class="kpi-desc">Curated & Verified Content</span>
          </div>
        </div>

        <div class="c5-kpi-card" @click="selectedTopic = 'Generative AI & Python'" style="cursor:pointer">
          <div class="kpi-icon-wrap emerald">🤖</div>
          <div class="kpi-meta">
            <span class="kpi-val">24 AI & LLM Tracks</span>
            <span class="kpi-desc">PyTorch, LangChain & Agents</span>
          </div>
        </div>

        <div class="c5-kpi-card" @click="selectedTopic = 'Full-Stack Web Dev'" style="cursor:pointer">
          <div class="kpi-icon-wrap blue">🌐</div>
          <div class="kpi-meta">
            <span class="kpi-val">45 Web Frameworks</span>
            <span class="kpi-desc">Vue 3, React, Laravel, Node</span>
          </div>
        </div>

        <div class="c5-kpi-card" @click="activeView = 'resources'" style="cursor:pointer">
          <div class="kpi-icon-wrap amber">📚</div>
          <div class="kpi-meta">
            <span class="kpi-val">80+ Dev Cheat Sheets</span>
            <span class="kpi-desc">Docs, Sandboxes & Repos</span>
          </div>
        </div>
      </div>

      <!-- Main Navigation Tabs -->
      <div class="c5-view-tabs">
        <button
          :class="['c5-tab-btn', { active: activeView === 'courses' }]"
          @click="activeView = 'courses'"
        >
          <span>📚 Free Developer Courses</span>
          <span class="c-badge">{{ filteredCourses.length }}</span>
        </button>

        <button
          :class="['c5-tab-btn', { active: activeView === 'resources' }]"
          @click="activeView = 'resources'"
        >
          <span>🛠️ Developer Tools & Cheat Sheets</span>
          <span class="c-badge">{{ devResources.length }}</span>
        </button>

        <button
          :class="['c5-tab-btn', { active: activeView === 'roadmaps' }]"
          @click="activeView = 'roadmaps'"
        >
          <span>🗺️ Step-by-Step Learning Roadmaps</span>
          <span class="c-badge">6 Paths</span>
        </button>
      </div>

      <!-- SECTION 1: FREE ONLINE COURSES CATALOG -->
      <div v-if="activeView === 'courses'">
        <!-- Filter Controls -->
        <div class="c5-filter-bar">
          <div class="topics-pills-wrap">
            <button
              v-for="topic in topics"
              :key="topic"
              :class="['topic-pill', { active: selectedTopic === topic }]"
              @click="selectedTopic = topic"
            >
              {{ topic }}
            </button>
          </div>

          <div class="search-and-level">
            <div class="search-input-wrap">
              <span class="search-icon">🔍</span>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search courses (e.g. Vue, Laravel, Python, Docker, React)..."
                class="search-input"
              />
            </div>

            <select v-model="selectedLevel" class="level-select">
              <option value="All Levels">All Levels</option>
              <option value="Beginner">Beginner</option>
              <option value="Intermediate">Intermediate</option>
              <option value="Advanced">Advanced</option>
            </select>
          </div>
        </div>

        <!-- Course Cards Grid -->
        <div class="courses-grid">
          <div
            v-for="course in filteredCourses"
            :key="course.id"
            class="course-card"
          >
            <div class="course-card-top">
              <div class="topic-tag-wrap">
                <span class="topic-tag">{{ course.topic }}</span>
                <span :class="['level-tag', course.level.toLowerCase()]">{{ course.level }}</span>
              </div>
              <span class="free-badge">🎁 100% Free</span>
            </div>

            <h3 class="course-title">{{ course.title }}</h3>
            <div class="instructor-row">
              <span class="inst-icon">👨‍🏫</span>
              <span class="inst-name"><strong>{{ course.provider }}</strong> ({{ course.instructor }})</span>
            </div>

            <p class="course-desc">{{ course.description }}</p>

            <!-- Course Meta Stats -->
            <div class="course-meta-stats">
              <div class="cm-chip">
                <span class="cm-lbl">Duration</span>
                <strong class="cm-val">⏱️ {{ course.duration }}</strong>
              </div>
              <div class="cm-chip">
                <span class="cm-lbl">Lessons</span>
                <strong class="cm-val">📖 {{ course.lessons }} Lessons</strong>
              </div>
              <div class="cm-chip">
                <span class="cm-lbl">Certificate</span>
                <strong class="cm-val green">{{ course.hasCertificate ? '✓ Free Cert' : 'Self-Paced' }}</strong>
              </div>
            </div>

            <!-- Skills Covered Tags -->
            <div class="skills-tags-row">
              <span v-for="skill in course.skills" :key="skill" class="skill-tag">
                #{{ skill }}
              </span>
            </div>

            <!-- Card Bottom Buttons -->
            <div class="course-card-actions">
              <a
                :href="course.url"
                target="_blank"
                rel="noopener noreferrer"
                class="btn-start-course"
              >
                🚀 Start Free Course ↗
              </a>
              <button class="btn-syllabus" @click="openSyllabusModal(course)">
                📑 Syllabus
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- SECTION 2: DEVELOPER RESOURCES & CHEAT SHEETS -->
      <div v-else-if="activeView === 'resources'" class="resources-container">
        <div class="res-grid">
          <div
            v-for="res in devResources"
            :key="res.id"
            class="resource-card"
          >
            <div class="res-card-hdr">
              <span class="res-icon">{{ res.icon }}</span>
              <div>
                <h3 class="res-title">{{ res.title }}</h3>
                <span class="res-category">{{ res.category }}</span>
              </div>
            </div>
            <p class="res-desc">{{ res.description }}</p>
            <div class="res-tags">
              <span v-for="tag in res.tags" :key="tag" class="res-tag">{{ tag }}</span>
            </div>
            <a :href="res.url" target="_blank" rel="noopener noreferrer" class="btn-res-link">
              🌐 Open Resource & Documentation ↗
            </a>
          </div>
        </div>
      </div>

      <!-- SECTION 3: STEP-BY-STEP LEARNING ROADMAPS -->
      <div v-else class="roadmaps-container">
        <div class="roadmap-cards-grid">
          <div v-for="map in roadmaps" :key="map.id" class="roadmap-card">
            <div class="rm-hdr">
              <span class="rm-icon">{{ map.icon }}</span>
              <div>
                <h3 class="rm-title">{{ map.title }}</h3>
                <span class="rm-est">Estimated Duration: {{ map.duration }}</span>
              </div>
            </div>
            <p class="rm-desc">{{ map.description }}</p>

            <div class="rm-steps-list">
              <div v-for="(step, sIdx) in map.steps" :key="sIdx" class="rm-step-item">
                <span class="step-num">{{ sIdx + 1 }}</span>
                <div class="step-info">
                  <strong>{{ step.title }}</strong>
                  <span>{{ step.desc }}</span>
                </div>
              </div>
            </div>

            <a :href="map.officialRoadmap" target="_blank" rel="noopener noreferrer" class="btn-view-rm">
              🗺️ Open Interactive Visual Roadmap ↗
            </a>
          </div>
        </div>
      </div>

      <!-- MODAL 1: Course Syllabus Details Modal -->
      <div v-if="syllabusCourse" class="modal-backdrop" @click.self="syllabusCourse = null">
        <div class="syllabus-modal-card">
          <div class="modal-header">
            <div>
              <span class="topic-tag">{{ syllabusCourse.topic }}</span>
              <h2 class="modal-title">{{ syllabusCourse.title }}</h2>
              <span class="modal-sub">By {{ syllabusCourse.provider }} ({{ syllabusCourse.instructor }})</span>
            </div>
            <button class="close-btn" @click="syllabusCourse = null">✕</button>
          </div>

          <div class="syllabus-content">
            <h4 class="syl-sec-title">📖 What You Will Learn (Modules & Curriculum)</h4>
            <div class="syl-modules-list">
              <div v-for="(mod, mIdx) in syllabusCourse.curriculum" :key="mIdx" class="syl-mod-row">
                <span class="mod-num">Module {{ mIdx + 1 }}</span>
                <div class="mod-details">
                  <strong>{{ mod.name }}</strong>
                  <p>{{ mod.summary }}</p>
                </div>
              </div>
            </div>

            <div class="syl-actions-row">
              <a
                :href="syllabusCourse.url"
                target="_blank"
                rel="noopener noreferrer"
                class="btn-start-course"
                style="text-align:center"
              >
                🚀 Launch Free Course on {{ syllabusCourse.provider }} ↗
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL 2: Interactive Code Playground -->
      <div v-if="showPlayground" class="modal-backdrop" @click.self="showPlayground = false">
        <div class="playground-modal-card">
          <div class="modal-header">
            <div>
              <h2 class="modal-title">💻 Live Interactive Developer Sandbox</h2>
              <span class="modal-sub">Instant HTML/CSS/JavaScript live output runner</span>
            </div>
            <button class="close-btn" @click="showPlayground = false">✕</button>
          </div>

          <div class="playground-split">
            <div class="editor-pane">
              <label class="editor-label">Code Editor (HTML / JS / CSS)</label>
              <textarea v-model="playgroundCode" class="code-textarea" rows="12"></textarea>
            </div>
            <div class="preview-pane">
              <label class="editor-label">Live Output Preview</label>
              <iframe :srcdoc="playgroundCode" class="output-frame" title="Code Output"></iframe>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'C5DevAcademy',
  data() {
    return {
      displayName: 'Fawaz Alharbi',
      userAvatar: '',
      defaultAvatar: require('@/assets/Gittax/avatar.png'),
      isArabic: false,
      activeView: 'courses', // 'courses', 'resources', 'roadmaps'
      selectedTopic: 'All Topics',
      selectedLevel: 'All Levels',
      searchQuery: '',
      syllabusCourse: null,
      showPlayground: false,
      playgroundCode: `<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: sans-serif; background: #0f172a; color: #fff; text-align: center; padding: 30px; }
    h1 { color: #38bdf8; }
    button { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; }
  </style>
</head>
<body>
  <h1>🚀 Welcome to Fawaz Dev Academy!</h1>
  <p>Live interactive preview running successfully.</p>
  <button onclick="alert('Hello Fawaz Developer!')">Click Me!</button>
</body>
</html>`,
      topics: [
        'All Topics',
        'Full-Stack Web Dev',
        'Generative AI & Python',
        'Cloud & DevOps',
        'Mobile Development',
        'Cybersecurity & Ethical Hacking',
        'Databases & System Design'
      ],
      courses: [
        {
          id: 'C1',
          title: 'Full Stack Open 2026 (Modern Web Apps with React, Node, GraphQL & TypeScript)',
          topic: 'Full-Stack Web Dev',
          provider: 'University of Helsinki',
          instructor: 'Department of Computer Science',
          level: 'Intermediate',
          duration: '60 Hours',
          lessons: 92,
          hasCertificate: true,
          url: 'https://fullstackopen.com/en/',
          description: 'Top-tier university course covering modern JavaScript web development, React, Redux, Node.js, Express, MongoDB, TypeScript, GraphQL, and CI/CD pipelines.',
          skills: ['React', 'Node.js', 'TypeScript', 'GraphQL', 'Docker', 'MongoDB'],
          curriculum: [
            { name: 'Introduction to React & Component State', summary: 'Fundamentals of JSX, component hierarchies, virtual DOM, and re-rendering hooks.' },
            { name: 'Communicating with Server & REST APIs', summary: 'Building REST backends with Node.js and Express, async handling, and error middleware.' },
            { name: 'State Management & TypeScript', summary: 'Global state with Redux Toolkit and full type-safety across client and server.' },
            { name: 'Continuous Integration & Production Deployment', summary: 'Automated GitHub Actions testing, containerization, and cloud deployment.' }
          ]
        },
        {
          id: 'C2',
          title: 'Harvard CS50: Introduction to Computer Science & Software Engineering',
          topic: 'Full-Stack Web Dev',
          provider: 'Harvard University / edX',
          instructor: 'Prof. David J. Malan',
          level: 'Beginner',
          duration: '45 Hours',
          lessons: 64,
          hasCertificate: true,
          url: 'https://pll.harvard.edu/course/cs50-introduction-computer-science',
          description: 'The world-famous entry-level computer science course teaching computational thinking, algorithms, memory management, C, Python, SQL, and Flask.',
          skills: ['Algorithms', 'C Programming', 'Python', 'SQL', 'Memory Management', 'Data Structures'],
          curriculum: [
            { name: 'Computational Thinking & C Basics', summary: 'Pointers, dynamic memory allocation, and algorithmic complexity.' },
            { name: 'Data Structures & Big O Notation', summary: 'Linked lists, hash tables, binary search trees, and sorting algorithms.' },
            { name: 'Python & Web Development', summary: 'Transition to high-level scripting, object-oriented design, and database queries.' }
          ]
        },
        {
          id: 'C3',
          title: 'Practical Deep Learning & Generative AI for Coders (PyTorch & LLMs)',
          topic: 'Generative AI & Python',
          provider: 'fast.ai / Jeremy Howard',
          instructor: 'Jeremy Howard (Ex-Kaggle President)',
          level: 'Intermediate',
          duration: '35 Hours',
          lessons: 48,
          hasCertificate: false,
          url: 'https://course.fast.ai/',
          description: 'Build state-of-the-art computer vision, NLP, and generative AI models using PyTorch, Hugging Face transformers, and fine-tuning techniques.',
          skills: ['PyTorch', 'Generative AI', 'Transformers', 'Hugging Face', 'Computer Vision', 'Fine-Tuning'],
          curriculum: [
            { name: 'Deep Learning Fundamentals with PyTorch', summary: 'Training neural networks, gradient descent, loss functions, and transfer learning.' },
            { name: 'Natural Language Processing & Transformers', summary: 'Self-attention mechanisms, LLM tokenization, and domain fine-tuning.' },
            { name: 'Deploying AI Models to Production', summary: 'Packaging models with ONNX, FastAPI, and GPU cloud inference.' }
          ]
        },
        {
          id: 'C4',
          title: 'Laravel 11 & Vue 3 Full-Stack Enterprise Masterclass',
          topic: 'Full-Stack Web Dev',
          provider: 'Laracasts / Open Community',
          instructor: 'Jeffrey Way',
          level: 'Beginner',
          duration: '28 Hours',
          lessons: 55,
          hasCertificate: true,
          url: 'https://laracasts.com/series/30-days-to-learn-laravel-11',
          description: 'Comprehensive guide to building scalable, full-featured web platforms with Laravel 11, Inertia.js, Vue 3, Sanctum API authentication, and MySQL.',
          skills: ['Laravel 11', 'Vue 3', 'PHP 8.3', 'Sanctum Auth', 'Eloquent ORM', 'Tailwind CSS'],
          curriculum: [
            { name: 'Modern PHP 8 & Eloquent Relationships', summary: 'Database migrations, seeders, foreign keys, and performant query builders.' },
            { name: 'Building RESTful APIs with Sanctum', summary: 'Token authentication, middleware role guards, and resource transformers.' },
            { name: 'Vue 3 Composition API & State Management', summary: 'Reactive components, Pinia/Vuex stores, and smooth single-page UX.' }
          ]
        },
        {
          id: 'C5',
          title: 'Docker & Kubernetes DevOps Bootcamp for Cloud Engineers',
          topic: 'Cloud & DevOps',
          provider: 'freeCodeCamp / TechWorld with Nana',
          instructor: 'Nana Janashia',
          level: 'Intermediate',
          duration: '20 Hours',
          lessons: 38,
          hasCertificate: true,
          url: 'https://www.freecodecamp.org/news/learn-docker-and-kubernetes-hands-on/',
          description: 'Master containerization and cluster orchestration from zero. Learn Dockerfile optimization, multi-container compose, Pods, Deployments, Ingress, and Helm.',
          skills: ['Docker', 'Kubernetes', 'Helm', 'CI/CD', 'Nginx Ingress', 'Microservices'],
          curriculum: [
            { name: 'Containerization Mastery', summary: 'Docker images, multi-stage builds, volume mounts, and network bridging.' },
            { name: 'Kubernetes Architecture', summary: 'Control planes, worker nodes, Kubelet, services, and ConfigMaps.' },
            { name: 'Production Orchestration', summary: 'Auto-scaling, rolling updates, zero-downtime deployments, and persistent volumes.' }
          ]
        },
        {
          id: 'C6',
          title: 'Practical Ethical Hacking & Web Application Penetration Testing',
          topic: 'Cybersecurity & Ethical Hacking',
          provider: 'TCM Security / Cybrary Free',
          instructor: 'Heath Adams (The Cyber Mentor)',
          level: 'Beginner',
          duration: '25 Hours',
          lessons: 42,
          hasCertificate: true,
          url: 'https://www.youtube.com/watch?v=3Kq1MIfTWCE',
          description: 'Learn ethical hacking methodologies, reconnaissance, OWASP Top 10 vulnerabilities (SQLi, XSS, CSRF), network scanning with Nmap, and exploit development.',
          skills: ['OWASP Top 10', 'Penetration Testing', 'Nmap', 'Burp Suite', 'Network Security', 'Linux Security'],
          curriculum: [
            { name: 'Network Reconnaissance & Scanning', summary: 'Port scanning, OS fingerprinting, and vulnerability scanning with Nmap.' },
            { name: 'Web Application Attacks', summary: 'SQL injection, cross-site scripting (XSS), IDOR, and authentication bypasses.' },
            { name: 'Securing Applications & Hardening', summary: 'Input sanitization, CSP headers, rate-limiting, and security patch audits.' }
          ]
        }
      ],
      devResources: [
        {
          id: 'R1',
          title: 'MDN Web Docs (Mozilla Developer Network)',
          category: 'Documentation & Reference',
          icon: '📖',
          description: 'The authoritative source for HTML5, CSS3, JavaScript APIs, Web APIs, and browser compatibility tables.',
          tags: ['HTML5', 'CSS3', 'JavaScript', 'Web APIs'],
          url: 'https://developer.mozilla.org/'
        },
        {
          id: 'R2',
          title: 'roadmap.sh — Interactive Developer Learning Paths',
          category: 'Career Roadmaps',
          icon: '🗺️',
          description: 'Community-driven visual roadmaps, guides, and learning paths for Frontend, Backend, DevOps, AI, and QA.',
          tags: ['Roadmaps', 'Career Path', 'Architecture'],
          url: 'https://roadmap.sh/'
        },
        {
          id: 'R3',
          title: 'Vue.js 3 & Laravel Official Ecosystem Docs',
          category: 'Framework Docs',
          icon: '⚡',
          description: 'Complete documentation for Vue 3 Composition API, Vite, Pinia, Laravel 11, Eloquent, and Sanctum.',
          tags: ['Vue 3', 'Laravel 11', 'Vite', 'Pinia'],
          url: 'https://vuejs.org/'
        },
        {
          id: 'R4',
          title: 'Hugging Face Open AI Models & Datasets Hub',
          category: 'AI & Machine Learning',
          icon: '🤗',
          description: 'Open-source platform with 500k+ pre-trained LLM models, datasets, spaces, and transformer pipelines.',
          tags: ['Hugging Face', 'LLMs', 'PyTorch', 'Transformers'],
          url: 'https://huggingface.co/'
        },
        {
          id: 'R5',
          title: 'LeetCode & HackerRank Free Problem Solving Hub',
          category: 'Coding Practice & Algorithms',
          icon: '💡',
          description: 'Solve 2,000+ data structures and algorithms challenges with automated test suites and discussion solutions.',
          tags: ['Algorithms', 'Data Structures', 'Interview Prep'],
          url: 'https://leetcode.com/'
        },
        {
          id: 'R6',
          title: 'GitHub Free For Developers Directory',
          category: 'Free Developer Tools',
          icon: '🎁',
          description: 'Massive curated collection of free SaaS, PaaS, cloud hosting, CI/CD, monitoring, and database tiers for developers.',
          tags: ['Free Hosting', 'PaaS', 'APIs', 'Cloud Tiers'],
          url: 'https://free-for.dev/'
        }
      ],
      roadmaps: [
        {
          id: 'RM1',
          title: 'Full-Stack Web Developer Path',
          icon: '🌐',
          duration: '6 - 9 Months',
          description: 'From absolute fundamentals to architecting distributed enterprise web platforms.',
          officialRoadmap: 'https://roadmap.sh/full-stack',
          steps: [
            { title: 'HTML5, CSS3 & Responsive UI', desc: 'Semantic HTML, Flexbox, CSS Grid, and responsive viewport design.' },
            { title: 'JavaScript (ES6+) & TypeScript', desc: 'Async/Await, DOM manipulation, closures, and static typing.' },
            { title: 'Frontend Framework (Vue 3 / React)', desc: 'Component architecture, reactivity, props, and global state management.' },
            { title: 'Backend REST API (Laravel / Node)', desc: 'MVC architecture, routing, middleware, controllers, and JWT/Sanctum auth.' },
            { title: 'Database Design & Deployment', desc: 'PostgreSQL/MySQL indexing, Docker containerization, and cloud hosting.' }
          ]
        },
        {
          id: 'RM2',
          title: 'AI & Machine Learning Engineer Path',
          icon: '🤖',
          duration: '8 - 12 Months',
          description: 'Master mathematical foundations, PyTorch, Large Language Models, and production inference.',
          officialRoadmap: 'https://roadmap.sh/ai-data-scientist',
          steps: [
            { title: 'Python & Scientific Computing', desc: 'NumPy, Pandas, Matplotlib, and data preprocessing pipelines.' },
            { title: 'Mathematics for ML', desc: 'Linear algebra, multivariable calculus, probability, and statistics.' },
            { title: 'Deep Learning with PyTorch', desc: 'Feedforward networks, CNNs for vision, RNNs/Transformers for NLP.' },
            { title: 'Generative AI & LLM Fine-Tuning', desc: 'RAG (Retrieval-Augmented Generation), LangChain, LoRA, and vector DBs.' },
            { title: 'Model MLOps & Production Inference', desc: 'FastAPI, Docker, Triton Inference Server, and GPU optimization.' }
          ]
        },
        {
          id: 'RM3',
          title: 'DevOps & Cloud Architect Path',
          icon: '☁️',
          duration: '6 - 8 Months',
          description: 'Automate infrastructure, scale microservices clusters, and establish zero-downtime CI/CD.',
          officialRoadmap: 'https://roadmap.sh/devops',
          steps: [
            { title: 'Linux Server Administration & Bash', desc: 'Process management, SSH keys, permissions, and network sockets.' },
            { title: 'Docker Containerization', desc: 'Multi-stage builds, compose files, volume persistence, and networking.' },
            { title: 'Kubernetes Cluster Orchestration', desc: 'Pods, Deployments, Services, ConfigMaps, Ingress, and Helm charts.' },
            { title: 'CI/CD Automation & Infrastructure as Code', desc: 'GitHub Actions, Terraform, AWS/GCP cloud resources, and Prometheus monitoring.' }
          ]
        }
      ]
    };
  },
  computed: {
    ...mapState({
      user:       state => state.auth ? state.auth.user : null,
      isDarkMode: state => state.settings ? state.settings.isDarkMode : false,
      locale:     state => state.settings ? state.settings.locale : 'en',
    }),
    currentUser() {
      return this.user || JSON.parse(localStorage.getItem('loggedInUser'));
    },
    filteredCourses() {
      return this.courses.filter(c => {
        if (this.selectedTopic !== 'All Topics' && c.topic !== this.selectedTopic) return false;
        if (this.selectedLevel !== 'All Levels' && c.level !== this.selectedLevel) return false;

        const q = this.searchQuery.toLowerCase();
        if (q) {
          const matchTitle = c.title.toLowerCase().includes(q);
          const matchDesc = c.description.toLowerCase().includes(q);
          const matchInst = c.instructor.toLowerCase().includes(q);
          const matchProv = c.provider.toLowerCase().includes(q);
          const matchSkill = c.skills.some(s => s.toLowerCase().includes(q));
          if (!matchTitle && !matchDesc && !matchInst && !matchProv && !matchSkill) return false;
        }
        return true;
      });
    }
  },
  mounted() {
    this.isArabic = this.locale === 'ar' || (this.$i18n && this.$i18n.locale === 'ar');
    document.documentElement.dir = this.isArabic ? 'rtl' : 'ltr';
    document.body.classList.toggle('rtl', this.isArabic);

    if (this.currentUser) {
      this.displayName = this.currentUser.name || 'Fawaz Alharbi';
      this.userAvatar = this.currentUser.picture || this.currentUser.avatar || '';
    }
  },
  methods: {
    ...mapActions(['toggleDarkMode', 'setLocale', 'logout']),

    toggleLanguage() {
      const newLocale = this.isArabic ? 'en' : 'ar';
      this.setLanguage(newLocale);
    },

    setLanguage(locale) {
      this.isArabic = locale === 'ar';
      this.$i18n.locale = locale;
      this.setLocale(locale);
      document.documentElement.dir = this.isArabic ? 'rtl' : 'ltr';
      document.body.classList.toggle('rtl', this.isArabic);
    },

    openSyllabusModal(course) {
      this.syllabusCourse = course;
    },

    openRoadmapModal() {
      this.activeView = 'roadmaps';
    },

    openCodePlayground() {
      this.showPlayground = true;
    },

    async handleLogout() {
      try {
        await this.logout();
      } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('loggedInUser');
        this.$router.push('/login').catch(() => {
          window.location.href = '/login';
        });
      }
    }
  }
};
</script>

<style scoped>
.c5-page-root {
  min-height: 100vh;
  background: #f8fafc;
  color: #0f172a;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.c5-page-root.dark {
  background: #090d16;
  color: #f8fafc;
}
.c5-page-root.rtl { direction: rtl; }

/* Navbar */
.c5-navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
}
.dark .c5-navbar {
  background: #111827;
  border-bottom-color: #1f2937;
}

.nav-left { display: flex; align-items: center; gap: 12px; }
.brand-link { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; }
.brand-logo { height: 32px; }
.brand-name { font-size: 1.05rem; font-weight: 800; color: #0284c7; }
.nav-divider { color: #94a3b8; }
.nav-c5-badge { font-size: 0.85rem; font-weight: 800; color: #6366f1; }

.nav-right { display: flex; align-items: center; gap: 10px; }
.nav-pill-btn {
  padding: 6px 12px;
  border-radius: 20px;
  background: #f1f5f9;
  color: #475569;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 700;
  transition: all 0.2s;
}
.dark .nav-pill-btn { background: #1f2937; color: #e2e8f0; }
.nav-pill-btn:hover { background: #6366f1; color: #ffffff; }

.nav-icon-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 50%;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.dark .nav-icon-btn { background: #1f2937; border-color: #374151; color: #ffffff; }

.nav-user-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px 4px 4px;
  border-radius: 30px;
  background: #f1f5f9;
}
.dark .nav-user-pill { background: #1f2937; }
.user-avatar-sm { width: 26px; height: 26px; border-radius: 50%; }
.user-name-sm { font-size: 0.8rem; font-weight: 700; }

.nav-logout-btn {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
}
.nav-logout-btn:hover { background: #ef4444; color: #fff; }

/* Ticker */
.academy-ticker {
  background: #1e1b4b;
  color: #c7d2fe;
  padding: 8px 24px;
  font-size: 0.8rem;
  overflow-x: auto;
  white-space: nowrap;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.ticker-track { display: flex; gap: 16px; align-items: center; }
.t-sep { color: #818cf8; }

/* Main Container */
.c5-main-container {
  max-width: 1300px;
  margin: 0 auto;
  padding: 28px 20px 60px;
}

.c5-hero-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}
.badge-row { display: flex; gap: 8px; margin-bottom: 8px; }
.badge-c5 {
  background: rgba(99, 102, 241, 0.15);
  color: #6366f1;
  font-size: 0.75rem;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 20px;
}
.badge-free {
  background: #dcfce7;
  color: #166534;
  font-size: 0.72rem;
  font-weight: 800;
  padding: 4px 8px;
  border-radius: 20px;
}

.c5-title { font-size: 1.8rem; font-weight: 900; margin: 0 0 6px 0; letter-spacing: -0.5px; }
.c5-subtitle { font-size: 0.9rem; color: #64748b; margin: 0; max-width: 780px; }
.dark .c5-subtitle { color: #94a3b8; }

.hero-right-actions { display: flex; gap: 10px; }
.c5-primary-btn {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: #ffffff;
  border: none;
  padding: 10px 18px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.c5-secondary-btn {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #334155;
  padding: 10px 18px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
}
.dark .c5-secondary-btn { background: #111827; border-color: #374151; color: #e2e8f0; }

/* KPI Grid */
.c5-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 18px;
  margin-bottom: 24px;
}
.c5-kpi-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  transition: all 0.2s;
}
.c5-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.06); }
.dark .c5-kpi-card { background: #111827; border-color: #1f2937; }

.kpi-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}
.kpi-icon-wrap.indigo  { background: rgba(99, 102, 241, 0.15); }
.kpi-icon-wrap.emerald { background: rgba(16, 185, 129, 0.15); }
.kpi-icon-wrap.blue    { background: rgba(2, 132, 199, 0.15); }
.kpi-icon-wrap.amber   { background: rgba(217, 119, 6, 0.15); }

.kpi-meta { display: flex; flex-direction: column; }
.kpi-val { font-size: 1.25rem; font-weight: 900; }
.kpi-desc { font-size: 0.75rem; color: #64748b; }
.dark .kpi-desc { color: #94a3b8; }

/* View Tabs */
.c5-view-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 18px;
  overflow-x: auto;
}
.c5-tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.dark .c5-tab-btn { background: #111827; border-color: #374151; color: #cbd5e1; }
.c5-tab-btn.active {
  background: #6366f1;
  color: #ffffff;
  border-color: #6366f1;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}
.c-badge {
  background: rgba(0, 0, 0, 0.08);
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 10px;
}
.c5-tab-btn.active .c-badge { background: rgba(255, 255, 255, 0.3); color: #ffffff; }

/* Filter Bar */
.c5-filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 14px;
}
.topics-pills-wrap { display: flex; gap: 6px; flex-wrap: wrap; }
.topic-pill {
  padding: 6px 12px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
}
.dark .topic-pill { background: #111827; border-color: #374151; color: #cbd5e1; }
.topic-pill.active { background: #6366f1; color: #ffffff; border-color: #6366f1; }

.search-and-level { display: flex; align-items: center; gap: 10px; }
.search-input-wrap { position: relative; min-width: 280px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.85rem; }
.rtl .search-icon { left: auto; right: 10px; }
.search-input {
  width: 100%;
  padding: 8px 12px 8px 34px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-size: 0.85rem;
  outline: none;
}
.rtl .search-input { padding: 8px 34px 8px 12px; }
.dark .search-input { background: #111827; border-color: #374151; }

.level-select {
  padding: 8px 12px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: inherit;
  font-size: 0.85rem;
  outline: none;
}
.dark .level-select { background: #111827; border-color: #374151; }

/* Courses Grid */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  gap: 22px;
}
.course-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: all 0.2s;
}
.course-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08); }
.dark .course-card { background: #111827; border-color: #1f2937; }

.course-card-top { display: flex; justify-content: space-between; align-items: center; }
.topic-tag-wrap { display: flex; gap: 6px; }
.topic-tag {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
  font-size: 0.72rem;
  font-weight: 800;
  padding: 4px 8px;
  border-radius: 6px;
}
.level-tag {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 6px;
  background: #f1f5f9;
}
.dark .level-tag { background: #1f2937; }
.level-tag.beginner { color: #10b981; }
.level-tag.intermediate { color: #0284c7; }
.level-tag.advanced { color: #f59e0b; }

.free-badge {
  background: #dcfce7;
  color: #166534;
  font-size: 0.72rem;
  font-weight: 900;
  padding: 4px 8px;
  border-radius: 12px;
}

.course-title { font-size: 1.05rem; font-weight: 800; margin: 0; line-height: 1.35; }
.instructor-row { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #64748b; }
.dark .instructor-row { color: #94a3b8; }
.course-desc { font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.4; }
.dark .course-desc { color: #94a3b8; }

.course-meta-stats {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 6px;
}
.cm-chip {
  background: #f8fafc;
  padding: 6px;
  border-radius: 8px;
  text-align: center;
  border: 1px solid #f1f5f9;
}
.dark .cm-chip { background: #1f2937; border-color: #374151; }
.cm-lbl { font-size: 0.65rem; color: #64748b; display: block; }
.dark .cm-lbl { color: #94a3b8; }
.cm-val { font-size: 0.78rem; font-weight: 800; }
.cm-val.green { color: #10b981; }

.skills-tags-row { display: flex; flex-wrap: wrap; gap: 4px; }
.skill-tag {
  background: #f1f5f9;
  font-size: 0.68rem;
  font-weight: 700;
  color: #475569;
  padding: 2px 6px;
  border-radius: 4px;
}
.dark .skill-tag { background: #1e293b; color: #cbd5e1; }

.course-card-actions { display: flex; gap: 8px; margin-top: auto; }
.btn-start-course {
  flex: 1;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: #ffffff;
  text-decoration: none;
  text-align: center;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 800;
  transition: all 0.15s;
  box-shadow: 0 4px 10px rgba(99, 102, 241, 0.25);
}
.btn-start-course:hover { transform: translateY(-1px); }
.btn-syllabus {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  padding: 8px 12px;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
}
.dark .btn-syllabus { background: #1f2937; border-color: #374151; color: #ffffff; }

/* Resources Grid */
.res-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
}
.resource-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.dark .resource-card { background: #111827; border-color: #1f2937; }
.res-card-hdr { display: flex; align-items: center; gap: 12px; }
.res-icon { font-size: 1.6rem; }
.res-title { font-size: 1rem; font-weight: 800; margin: 0; }
.res-category { font-size: 0.72rem; color: #6366f1; font-weight: 700; }
.res-desc { font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.4; }
.dark .res-desc { color: #94a3b8; }
.res-tags { display: flex; flex-wrap: wrap; gap: 4px; }
.res-tag { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.68rem; font-weight: 700; }
.dark .res-tag { background: #1f2937; color: #cbd5e1; }
.btn-res-link {
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  color: #1e293b;
  text-decoration: none;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 0.78rem;
  font-weight: 800;
  text-align: center;
  margin-top: auto;
}
.dark .btn-res-link { background: #1f2937; border-color: #374151; color: #e2e8f0; }

/* Roadmaps */
.roadmap-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  gap: 22px;
}
.roadmap-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 22px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.dark .roadmap-card { background: #111827; border-color: #1f2937; }
.rm-hdr { display: flex; align-items: center; gap: 12px; }
.rm-icon { font-size: 1.6rem; }
.rm-title { font-size: 1.1rem; font-weight: 800; margin: 0; }
.rm-est { font-size: 0.72rem; color: #10b981; font-weight: 700; }
.rm-desc { font-size: 0.8rem; color: #64748b; margin: 0; }
.dark .rm-desc { color: #94a3b8; }

.rm-steps-list { display: flex; flex-direction: column; gap: 8px; }
.rm-step-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #f8fafc;
  padding: 8px 12px;
  border-radius: 10px;
}
.dark .rm-step-item { background: #1a2234; }
.step-num {
  background: #6366f1;
  color: #fff;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 800;
  flex-shrink: 0;
}
.step-info { display: flex; flex-direction: column; font-size: 0.75rem; }
.step-info strong { font-weight: 800; margin-bottom: 2px; }
.step-info span { color: #64748b; }
.dark .step-info span { color: #94a3b8; }

.btn-view-rm {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: #ffffff;
  text-decoration: none;
  padding: 10px;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 800;
  text-align: center;
  margin-top: auto;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 20px;
}
.syllabus-modal-card {
  background: #ffffff;
  width: 100%;
  max-width: 640px;
  border-radius: 20px;
  padding: 28px;
  max-height: 85vh;
  overflow-y: auto;
}
.dark .syllabus-modal-card { background: #111827; color: #f8fafc; }

.modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
.modal-title { font-size: 1.15rem; font-weight: 800; margin: 4px 0 0 0; }
.modal-sub { font-size: 0.75rem; color: #64748b; }
.close-btn { background: #f1f5f9; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; }
.dark .close-btn { background: #1f2937; color: #fff; }

.syl-sec-title { font-size: 0.85rem; font-weight: 800; margin: 0 0 10px 0; }
.syl-modules-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; }
.syl-mod-row {
  background: #f8fafc;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}
.dark .syl-mod-row { background: #1a2234; border-color: #2d3748; }
.mod-num { font-size: 0.7rem; font-weight: 800; color: #6366f1; display: block; margin-bottom: 2px; }
.mod-details strong { font-size: 0.82rem; display: block; margin-bottom: 2px; }
.mod-details p { font-size: 0.75rem; color: #64748b; margin: 0; line-height: 1.3; }
.dark .mod-details p { color: #94a3b8; }

.playground-modal-card {
  background: #ffffff;
  width: 100%;
  max-width: 850px;
  border-radius: 20px;
  padding: 24px;
}
.dark .playground-modal-card { background: #111827; color: #f8fafc; }
.playground-split { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 14px; }
.editor-label { font-size: 0.75rem; font-weight: 800; margin-bottom: 6px; display: block; }
.code-textarea {
  width: 100%;
  font-family: monospace;
  font-size: 0.8rem;
  background: #0f172a;
  color: #38bdf8;
  border-radius: 10px;
  padding: 12px;
  border: 1px solid #334155;
  outline: none;
  resize: vertical;
}
.output-frame {
  width: 100%;
  height: 250px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #fff;
}
</style>
