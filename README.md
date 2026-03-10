# Student Management System

This repository is split into two runnable packages:

- `mac/` for macOS (Homebrew MySQL + PHP built-in server)
- `windows/` for Windows (WAMP + Apache + MySQL)

## Choose your platform
- macOS guide: `mac/README_MAC_SETUP.md`
- Windows guide: `windows/TUTORIAL_WINDOWS.md`

## Quick commands
macOS:
```bash
cd mac
chmod +x scripts/*.sh
./scripts/dev_up.sh
```

Windows:
```powershell
powershell -ExecutionPolicy Bypass -File .\windows\scripts\launch_wamp.ps1
```

## License
This project is for educational purposes.
