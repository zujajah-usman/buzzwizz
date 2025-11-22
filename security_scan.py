#!/usr/bin/env python3
"""
Comprehensive Security Vulnerability Scanner
Scans PHP files for common security issues (Codacy-style analysis)
"""

import os
import re
from collections import defaultdict

issues = defaultdict(list)
scanned_files = 0

def scan_file(filepath):
    global issues, scanned_files
    scanned_files += 1
    
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            lines = content.split('\n')
    except Exception as e:
        return
    
    file_issues = []
    
    for line_num, line in enumerate(lines, 1):
        original_line = line
        line = line.strip()
        
        # Skip comments
        if line.startswith('//') or line.startswith('/*') or line.startswith('*'):
            continue
        
        # Check for direct $_GET/$_POST/$_REQUEST usage without sanitization
        if re.search(r'\$_((GET|POST|REQUEST|COOKIE|SERVER)\[.*?\]|FILES\[.*?\])', line):
            # Check if sanitization functions are used
            if not re.search(r'(sanitize_input|validate_email|validate_int|escape_html|htmlspecialchars|filter_var|get_post|get_get|require_once.*security)', line):
                # Check if it's in a comment
                if not re.match(r'^\s*//', original_line):
                    file_issues.append({
                        'line': line_num,
                        'type': 'UNSANITIZED_INPUT',
                        'severity': 'HIGH',
                        'message': f'User input used without sanitization: {line[:80]}'
                    })
        
        # Check for echo/print with user input
        if re.search(r'(echo|print).*\$_(GET|POST|REQUEST|COOKIE|SERVER|FILES)', line):
            if not re.search(r'escape_html|htmlspecialchars', line):
                if not re.match(r'^\s*//', original_line):
                    file_issues.append({
                        'line': line_num,
                        'type': 'XSS_VULNERABILITY',
                        'severity': 'CRITICAL',
                        'message': f'Potential XSS: Outputting user input without escaping: {line[:80]}'
                    })
        
        # Check for SQL queries with string concatenation (not using prepared statements)
        if re.search(r'\b(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER).*\$_(GET|POST|REQUEST)', line, re.I):
            if not re.search(r'prepare|bindParam|bindValue|execute\(\[', line):
                if not re.match(r'^\s*//', original_line):
                    file_issues.append({
                        'line': line_num,
                        'type': 'SQL_INJECTION',
                        'severity': 'CRITICAL',
                        'message': f'Potential SQL injection: User input in SQL without prepared statements: {line[:80]}'
                    })
        
        # Check for file operations with user input
        if re.search(r'(include|require|file_get_contents|fopen|readfile|file_put_contents).*\$_(GET|POST|REQUEST)', line, re.I):
            if not re.search(r'sanitize|validate|basename', line):
                if not re.match(r'^\s*//', original_line):
                    file_issues.append({
                        'line': line_num,
                        'type': 'FILE_INCLUSION',
                        'severity': 'CRITICAL',
                        'message': f'Potential file inclusion vulnerability: {line[:80]}'
                    })
        
        # Check for eval()
        if re.search(r'\beval\s*\(', line):
            if not re.match(r'^\s*//', original_line):
                file_issues.append({
                    'line': line_num,
                    'type': 'CODE_INJECTION',
                    'severity': 'CRITICAL',
                    'message': f'CRITICAL: eval() function used - code injection risk: {line[:80]}'
                })
        
        # Check for hardcoded passwords/credentials
        if re.search(r'(password|passwd|pwd|secret|api_key)\s*=\s*["\'][^"\']{3,}["\']', line, re.I):
            if not re.search(r'getenv|env\(|config\[|TODO.*environment', line):
                if not re.match(r'^\s*//', original_line):
                    file_issues.append({
                        'line': line_num,
                        'type': 'HARDCODED_CREDENTIALS',
                        'severity': 'HIGH',
                        'message': f'Potential hardcoded credentials: {line[:80]}'
                    })
        
        # Check for error messages exposing system info
        if re.search(r'(echo|print|die).*\$e->getMessage\(\)', line):
            if not re.search(r'error_log|log_error|SECURITY.*expose', line):
                if not re.match(r'^\s*//', original_line):
                    file_issues.append({
                        'line': line_num,
                        'type': 'INFORMATION_DISCLOSURE',
                        'severity': 'MEDIUM',
                        'message': f'Error message exposed to user: {line[:80]}'
                    })
    
    if file_issues:
        issues[filepath] = file_issues

def scan_directory(directory):
    skip_dirs = {'vendor', 'bower_components', 'node_modules', 'tcpdf', 'recaptcha', '.git', '__pycache__', 'dist', 'build'}
    
    for root, dirs, files in os.walk(directory):
        # Remove skip directories from dirs list
        dirs[:] = [d for d in dirs if d not in skip_dirs]
        
        for file in files:
            if file.endswith('.php'):
                filepath = os.path.join(root, file)
                scan_file(filepath)

# Main execution
print("Starting comprehensive security scan (Codacy-style)...\n")
scan_directory('.')

# Display results
total_issues = 0
critical = 0
high = 0
medium = 0

for file, file_issues in sorted(issues.items()):
    print(f"=== {file} ===")
    for issue in file_issues:
        total_issues += 1
        if issue['severity'] == 'CRITICAL':
            critical += 1
        elif issue['severity'] == 'HIGH':
            high += 1
        elif issue['severity'] == 'MEDIUM':
            medium += 1
        
        print(f"[{issue['severity']}] Line {issue['line']}: {issue['type']}")
        print(f"  {issue['message']}\n")

print("\n=== SCAN SUMMARY ===")
print(f"Files scanned: {scanned_files}")
print(f"Total issues found: {total_issues}")
print(f"  - CRITICAL: {critical}")
print(f"  - HIGH: {high}")
print(f"  - MEDIUM: {medium}")

if total_issues > 0:
    print("\n⚠️  Security vulnerabilities found! Please review and fix.")
    exit(1)
else:
    print("\n✓ No security vulnerabilities found!")
    exit(0)

