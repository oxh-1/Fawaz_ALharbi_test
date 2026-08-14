<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    private array $courses = [
        [
            'id' => 'C1',
            'title' => 'Full Stack Open 2026 (Modern Web Apps with React, Node, GraphQL & TypeScript)',
            'topic' => 'Full-Stack Web Dev',
            'provider' => 'University of Helsinki',
            'instructor' => 'Department of Computer Science',
            'level' => 'Intermediate',
            'duration' => '60 Hours',
            'lessons' => 92,
            'hasCertificate' => true,
            'url' => 'https://fullstackopen.com/en/',
            'description' => 'Top-tier university course covering modern JavaScript web development, React, Redux, Node.js, Express, MongoDB, TypeScript, GraphQL, and CI/CD pipelines.',
            'skills' => ['React', 'Node.js', 'TypeScript', 'GraphQL', 'Docker', 'MongoDB'],
            'isFree' => true
        ],
        [
            'id' => 'C2',
            'title' => 'Harvard CS50: Introduction to Computer Science & Software Engineering',
            'topic' => 'Full-Stack Web Dev',
            'provider' => 'Harvard University / edX',
            'instructor' => 'Prof. David J. Malan',
            'level' => 'Beginner',
            'duration' => '45 Hours',
            'lessons' => 64,
            'hasCertificate' => true,
            'url' => 'https://pll.harvard.edu/course/cs50-introduction-computer-science',
            'description' => 'The world-famous entry-level computer science course teaching computational thinking, algorithms, memory management, C, Python, SQL, and Flask.',
            'skills' => ['Algorithms', 'C Programming', 'Python', 'SQL', 'Memory Management', 'Data Structures'],
            'isFree' => true
        ],
        [
            'id' => 'C3',
            'title' => 'Practical Deep Learning & Generative AI for Coders (PyTorch & LLMs)',
            'topic' => 'Generative AI & Python',
            'provider' => 'fast.ai / Jeremy Howard',
            'instructor' => 'Jeremy Howard (Ex-Kaggle President)',
            'level' => 'Intermediate',
            'duration' => '35 Hours',
            'lessons' => 48,
            'hasCertificate' => false,
            'url' => 'https://course.fast.ai/',
            'description' => 'Build state-of-the-art computer vision, NLP, and generative AI models using PyTorch, Hugging Face transformers, and fine-tuning techniques.',
            'skills' => ['PyTorch', 'Generative AI', 'Transformers', 'Hugging Face', 'Computer Vision', 'Fine-Tuning'],
            'isFree' => true
        ],
        [
            'id' => 'C4',
            'title' => 'Laravel 11 & Vue 3 Full-Stack Enterprise Masterclass',
            'topic' => 'Full-Stack Web Dev',
            'provider' => 'Laracasts / Open Community',
            'instructor' => 'Jeffrey Way',
            'level' => 'Beginner',
            'duration' => '28 Hours',
            'lessons' => 55,
            'hasCertificate' => true,
            'url' => 'https://laracasts.com/series/30-days-to-learn-laravel-11',
            'description' => 'Comprehensive guide to building scalable, full-featured web platforms with Laravel 11, Inertia.js, Vue 3, Sanctum API authentication, and MySQL.',
            'skills' => ['Laravel 11', 'Vue 3', 'PHP 8.3', 'Sanctum Auth', 'Eloquent ORM', 'Tailwind CSS'],
            'isFree' => true
        ],
        [
            'id' => 'C5',
            'title' => 'Docker & Kubernetes DevOps Bootcamp for Cloud Engineers',
            'topic' => 'Cloud & DevOps',
            'provider' => 'freeCodeCamp / TechWorld with Nana',
            'instructor' => 'Nana Janashia',
            'level' => 'Intermediate',
            'duration' => '20 Hours',
            'lessons' => 38,
            'hasCertificate' => true,
            'url' => 'https://www.freecodecamp.org/news/learn-docker-and-kubernetes-hands-on/',
            'description' => 'Master containerization and cluster orchestration from zero. Learn Dockerfile optimization, multi-container compose, Pods, Deployments, Ingress, and Helm.',
            'skills' => ['Docker', 'Kubernetes', 'Helm', 'CI/CD', 'Nginx Ingress', 'Microservices'],
            'isFree' => true
        ],
        [
            'id' => 'C6',
            'title' => 'Practical Ethical Hacking & Web Application Penetration Testing',
            'topic' => 'Cybersecurity & Ethical Hacking',
            'provider' => 'TCM Security / Cybrary Free',
            'instructor' => 'Heath Adams (The Cyber Mentor)',
            'level' => 'Beginner',
            'duration' => '25 Hours',
            'lessons' => 42,
            'hasCertificate' => true,
            'url' => 'https://www.youtube.com/watch?v=3Kq1MIfTWCE',
            'description' => 'Learn ethical hacking methodologies, reconnaissance, OWASP Top 10 vulnerabilities (SQLi, XSS, CSRF), network scanning with Nmap, and exploit development.',
            'skills' => ['OWASP Top 10', 'Penetration Testing', 'Nmap', 'Burp Suite', 'Network Security', 'Linux Security'],
            'isFree' => true
        ]
    ];

    public function index(Request $request): JsonResponse
    {
        $topic = $request->query('topic');
        $level = $request->query('level');
        $search = strtolower($request->query('search', ''));

        $filtered = array_values(array_filter($this->courses, function ($c) use ($topic, $level, $search) {
            if ($topic && $topic !== 'All Topics' && $c['topic'] !== $topic) {
                return false;
            }
            if ($level && $level !== 'All Levels' && $c['level'] !== $level) {
                return false;
            }
            if ($search) {
                $matchTitle = str_contains(strtolower($c['title']), $search);
                $matchDesc = str_contains(strtolower($c['description']), $search);
                $matchProv = str_contains(strtolower($c['provider']), $search);
                if (!$matchTitle && !$matchDesc && !$matchProv) return false;
            }
            return true;
        }));

        return response()->json([
            'success' => true,
            'data' => $filtered,
            'stats' => [
                'total_courses' => count($this->courses),
                'free_certified' => count(array_filter($this->courses, fn($c) => $c['hasCertificate'])),
                'topics_count' => count(array_unique(array_column($this->courses, 'topic'))),
            ]
        ]);
    }
}
