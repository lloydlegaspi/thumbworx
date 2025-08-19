# Contributing to Thumbworx

Thank you for your interest in contributing to Thumbworx! This document provides guidelines and information for contributors.

## 🚀 Getting Started

### Prerequisites
- Docker and Docker Compose
- Git
- Basic knowledge of Laravel, Flask, and Next.js

### Local Development Setup

1. **Fork and Clone**
   ```bash
   git clone https://github.com/yourusername/thumbworx.git
   cd thumbworx
   ```

2. **Start Development Environment**
   ```bash
   cd infra
   docker-compose up --build
   ```

3. **Verify Setup**
   - Frontend: http://localhost:3000
   - Laravel API: http://localhost:8000
   - Flask API: http://localhost:5000

## 🛠️ Development Workflow

### Making Changes

1. **Create a Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   # or
   git checkout -b fix/issue-description
   ```

2. **Make Your Changes**
   - Follow existing code style
   - Add tests for new features
   - Update documentation if needed

3. **Test Your Changes**
   ```bash
   # Test all services
   docker-compose logs -f

   # Run specific tests
   docker-compose exec laravel php artisan test
   ```

4. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "feat: add new vehicle tracking feature"
   # or
   git commit -m "fix: resolve GPS coordinate parsing issue"
   ```

5. **Push and Create PR**
   ```bash
   git push origin feature/your-feature-name
   ```

### Commit Message Convention

Use conventional commit format:
- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation changes
- `style:` - Code style changes
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Build process or auxiliary tool changes

## 📝 Code Style Guidelines

### Laravel (PHP)
- Follow PSR-12 coding standards
- Use PHP CS Fixer for formatting
- Add type hints where possible
- Write meaningful variable names

### Flask (Python)
- Follow PEP 8 style guide
- Use type hints (Python 3.6+)
- Keep functions small and focused
- Add docstrings for complex functions

### Next.js (TypeScript)
- Use TypeScript for type safety
- Follow React best practices
- Use functional components with hooks
- Keep components small and focused

### General Guidelines
- Write clear, self-documenting code
- Add comments for complex logic
- Keep functions under 50 lines when possible
- Use meaningful variable and function names

## 🧪 Testing

### Running Tests

```bash
# Laravel tests
docker-compose exec laravel php artisan test

# Flask tests (if implemented)
docker-compose exec flask python -m pytest

# Frontend tests (if implemented)
docker-compose exec next npm test
```

### Adding Tests
- Add unit tests for new functions
- Add integration tests for API endpoints
- Test error handling scenarios
- Verify edge cases

## 📚 Documentation

### Required Documentation Updates
- Update README.md for new features
- Add API documentation for new endpoints
- Update docker-compose.yml comments
- Add inline code comments for complex logic

### Documentation Style
- Use clear, concise language
- Include code examples
- Add screenshots for UI changes
- Keep documentation up to date

## 🐛 Reporting Issues

### Before Submitting an Issue
1. Check existing issues for duplicates
2. Test with the latest code
3. Include reproduction steps
4. Provide environment details

### Issue Template
```
**Description**
Brief description of the issue

**Steps to Reproduce**
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior**
What should happen

**Actual Behavior**
What actually happens

**Environment**
- OS: [e.g. Windows 10, macOS, Ubuntu]
- Docker version: [e.g. 20.10.8]
- Browser: [e.g. Chrome 96, Safari 15]

**Additional Context**
Any additional information
```

## 🔧 Pull Request Process

### Before Submitting
- [ ] Code follows style guidelines
- [ ] Tests pass locally
- [ ] Documentation updated
- [ ] No merge conflicts
- [ ] Branch is up to date with main

### PR Template
```
**Description**
Brief description of changes

**Type of Change**
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

**Testing**
- [ ] Unit tests added/updated
- [ ] Integration tests pass
- [ ] Manual testing completed

**Screenshots** (if applicable)
Add screenshots for UI changes

**Related Issues**
Closes #(issue_number)
```

### Review Process
1. Automated checks must pass
2. At least one code review required
3. All discussions resolved
4. Documentation updated
5. Tests passing

## 🚀 Deployment Guidelines

### Environment Considerations
- Test changes in local environment
- Consider impact on existing deployments
- Update environment variables if needed
- Verify database migrations

### Breaking Changes
- Document breaking changes clearly
- Provide migration guide
- Update version number appropriately
- Coordinate with maintainers

## 📞 Getting Help

### Communication Channels
- **GitHub Issues** - Bug reports and feature requests
- **GitHub Discussions** - General questions and ideas
- **Code Comments** - Implementation questions

### Response Times
- Issues: 24-48 hours for initial response
- PRs: 48-72 hours for review
- Questions: Best effort basis

## 🏆 Recognition

Contributors will be recognized in:
- README.md contributors section
- Release notes for significant contributions
- GitHub contributor statistics

## 📋 Development Checklist

### For New Features
- [ ] Feature implemented and tested
- [ ] Documentation updated
- [ ] Tests added
- [ ] No breaking changes (or documented)
- [ ] Performance impact considered
- [ ] Security implications reviewed

### For Bug Fixes
- [ ] Root cause identified
- [ ] Fix implemented
- [ ] Test added to prevent regression
- [ ] Documentation updated if needed
- [ ] Related issues referenced

Thank you for contributing to Thumbworx! 🚛📍
