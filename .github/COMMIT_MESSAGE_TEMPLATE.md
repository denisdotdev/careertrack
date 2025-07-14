# Commit Message Template

Please follow the [Conventional Commits](https://www.conventionalcommits.org/) specification for commit messages to enable automatic versioning.

## Format

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

## Types

- **feat**: A new feature (bumps minor version)
- **fix**: A bug fix (bumps patch version)
- **docs**: Documentation only changes
- **style**: Changes that do not affect the meaning of the code (white-space, formatting, missing semi-colons, etc)
- **refactor**: A code change that neither fixes a bug nor adds a feature
- **perf**: A code change that improves performance
- **test**: Adding missing tests or correcting existing tests
- **chore**: Changes to the build process or auxiliary tools and libraries such as documentation generation

## Examples

### Feature
```
feat: add user authentication system
feat(auth): implement JWT token validation
```

### Bug Fix
```
fix: resolve login form validation issue
fix(api): handle null response from external service
```

### Breaking Change
```
feat!: remove deprecated API endpoints

BREAKING CHANGE: The following endpoints have been removed:
- GET /api/v1/users/old
- POST /api/v1/users/legacy
```

### With Scope
```
feat(ui): add dark mode toggle
fix(database): resolve migration conflict
docs(readme): update installation instructions
```

## Branch Naming

For automatic versioning based on branch names:

- `feature/*` - Bumps minor version
- `hotfix/*` - Bumps patch version  
- `release/*` - Bumps major version

## Tips

1. Use the imperative mood in the subject line ("add" not "added" or "adds")
2. Don't capitalize the first letter of the subject line
3. Don't add a period at the end of the subject line
4. Separate subject from body with a blank line
5. Use the body to explain what and why vs. how 