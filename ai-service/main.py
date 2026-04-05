from __future__ import annotations

import os
import random
from typing import Any

from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

from model_infer import predict as ai_predict
from physics_core import flatten_features, simulate_collision

app = FastAPI(title="AIColl Physics + AI", version="1.0.0")

EXPECTED_TOKEN = os.environ.get("AI_SERVICE_TOKEN", "").strip()


class Vec2(BaseModel):
    x: float
    y: float


class SimulateIn(BaseModel):
    m1: float = Field(gt=0)
    m2: float = Field(gt=0)
    r1: float = Field(gt=0)
    r2: float = Field(gt=0)
    p1: Vec2
    p2: Vec2
    v1: Vec2
    v2: Vec2
    collision_type: str = "elastic"
    restitution: float = Field(default=1.0, ge=0, le=1)
    material: str = "steel"


def verify_token(x_service_token: str | None) -> None:
    if not EXPECTED_TOKEN:
        return
    if x_service_token != EXPECTED_TOKEN:
        raise HTTPException(status_code=401, detail="Invalid service token")


@app.post("/simulate")
def simulate(
    body: SimulateIn,
    x_service_token: str | None = Header(default=None, alias="X-Service-Token"),
) -> dict[str, Any]:
    verify_token(x_service_token)
    p = body.model_dump()
    return simulate_collision(p)


@app.post("/predict")
def predict(
    body: SimulateIn,
    x_service_token: str | None = Header(default=None, alias="X-Service-Token"),
) -> dict[str, Any]:
    verify_token(x_service_token)
    p = body.model_dump()
    return ai_predict(p)


class DatasetBatchIn(BaseModel):
    count: int = Field(ge=1, le=50_000)
    seed: int | None = None


@app.post("/dataset/batch")
def dataset_batch(
    body: DatasetBatchIn,
    x_service_token: str | None = Header(default=None, alias="X-Service-Token"),
) -> dict[str, Any]:
    verify_token(x_service_token)
    rng = random.Random(body.seed) if body.seed is not None else random.Random()
    rows: list[dict[str, Any]] = []
    for _ in range(body.count):
        m1 = rng.uniform(0.5, 5.0)
        m2 = rng.uniform(0.5, 5.0)
        r1 = rng.uniform(0.05, 0.5)
        r2 = rng.uniform(0.05, 0.5)
        gap = rng.uniform(0.01, 0.3)
        p1x = rng.uniform(-2.0, 0.0)
        p1y = rng.uniform(-0.5, 0.5)
        p2x = p1x + r1 + r2 + gap
        p2y = rng.uniform(-0.5, 0.5)
        v1x = rng.uniform(0.5, 8.0)
        v1y = rng.uniform(-0.8, 0.8)
        v2x = rng.uniform(-8.0, -0.2)
        v2y = rng.uniform(-0.8, 0.8)
        e = rng.uniform(0.2, 1.0)
        ctype = "elastic" if e >= 0.99 else "inelastic"
        if ctype == "elastic":
            e = 1.0
        payload = {
            "m1": m1,
            "m2": m2,
            "r1": r1,
            "r2": r2,
            "p1": {"x": p1x, "y": p1y},
            "p2": {"x": p2x, "y": p2y},
            "v1": {"x": v1x, "y": v1y},
            "v2": {"x": v2x, "y": v2y},
            "collision_type": ctype,
            "restitution": e,
            "material": "synthetic",
        }
        out = simulate_collision(payload)
        target = [
            out["v1"]["x"],
            out["v1"]["y"],
            out["v2"]["x"],
            out["v2"]["y"],
        ]
        rows.append({"input": payload, "features": flatten_features(payload), "target": target})
    return {"count": len(rows), "rows": rows}


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}
