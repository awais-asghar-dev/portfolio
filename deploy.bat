@echo off
echo Step 1: Staging portfolio changes...
git add .

echo Step 2: Committing updates...
git commit -m "Auto-update portfolio design"

echo Step 3: Pushing live to GitHub Pages...
git push origin main

echo Success! Your portfolio updates are live.
pause