1. # INITIAL SETUP (Run this ONCE per project)
   These commands set up Git in your local folder and link it to GitHub.
   IMPORTANT: Replace [YOUR_GITHUB_URL] with the actual HTTPS link from GitHub.
   Initialize Git in your project folder
   git init

Stage all files for the first commit

git add .

Create the very first snapshot (the commit message explains what you're saving)

git commit -m "feat: Initial project setup and file structure"

Set the remote connection to your new GitHub repository

git remote add origin [YOUR_GITHUB_URL]

Rename the local branch to 'main' (standard practice)

git branch -M main

Push the entire project history to GitHub (this is when you'll sign in)

git push -u origin main

2.  EVERYDAY UPDATES (Run this cycle every time you make changes)

This three-step cycle saves your changes locally and then pushes them to GitHub.

1. STAGE: Tell Git which files you modified and want to save

   git add .

2. COMMIT: Create a named snapshot of your changes
   Use prefixes like 'feat:' (for new features) or 'fix:' (for bug fixes)

   git commit -m "fix: Implemented parseInt() to correct input data type."

3. PUSH: Send the named snapshot (commit) to GitHub

   git push
