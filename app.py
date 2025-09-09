from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse, RedirectResponse
import yt_dlp

app = FastAPI()

def extract(url):
    ydl_opts = {"quiet": True, "skip_download": True}
    with yt_dlp.YoutubeDL(ydl_opts) as ydl:
        return ydl.extract_info(url, download=False)

@app.post("/parse")
async def parse(req: Request):
    body = await req.json()
    url = body.get("url")
    if not url:
        return JSONResponse({"error": "missing url"}, status_code=400)
    info = extract(url)
    return {
        "thumb": info.get("thumbnail"),
        "formats": [
            {"label": f"{f['height']}p", "type": f["ext"], "dl": f["url"]}
            for f in info.get("formats", [])
            if f.get("ext") == "mp4" and f.get("height") in [144, 240, 360, 720]
        ]
    }

@app.get("/get")
async def get(url: str, format: str):
    info = extract(url)
    for f in info.get("formats", []):
        if str(f.get("height")) == format and f.get("ext") == "mp4":
            return RedirectResponse(f["url"])
    return JSONResponse({"error": "format not found"}, status_code=404)
