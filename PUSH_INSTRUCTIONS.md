# Git Push Instructions

To push your changes to a remote repository, follow these steps:

## 1. Add Remote Repository
```bash
git remote add origin <your-repository-url>
```

Replace `<your-repository-url>` with your actual repository URL (e.g., GitHub, GitLab, Bitbucket).

## 2. Rename Branch to Main (Optional)
```bash
git branch -M main
```

## 3. Push to Remote Repository
```bash
git push -u origin main
```

## Alternative: Push to Master Branch
If you prefer to keep the master branch name:
```bash
git push -u origin master
```

## Subsequent Pushes
After the initial push, you can simply use:
```bash
git push
```