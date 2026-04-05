"""Tiny MLP inference; falls back to physics + noise if weights missing."""
from __future__ import annotations

import random
from pathlib import Path
from typing import Any

import torch
import torch.nn as nn

from physics_core import flatten_features, simulate_collision

DIR = Path(__file__).resolve().parent
WEIGHTS = DIR / "collision_mlp.pt"


class CollisionMLP(nn.Module):
    def __init__(self, in_dim: int = 13, hidden: int = 64, out_dim: int = 4):
        super().__init__()
        self.net = nn.Sequential(
            nn.Linear(in_dim, hidden),
            nn.ReLU(),
            nn.Linear(hidden, hidden),
            nn.ReLU(),
            nn.Linear(hidden, out_dim),
        )

    def forward(self, x: torch.Tensor) -> torch.Tensor:
        return self.net(x)


def _load_model() -> CollisionMLP | None:
    if not WEIGHTS.is_file():
        return None
    ckpt = torch.load(WEIGHTS, map_location="cpu")
    hidden = int(ckpt.get("hidden", 64))
    model = CollisionMLP(hidden=hidden)
    model.load_state_dict(ckpt["state_dict"])
    model.eval()
    return model


_MODEL: CollisionMLP | None = None


def get_model() -> CollisionMLP | None:
    global _MODEL
    if _MODEL is None:
        _MODEL = _load_model()
    return _MODEL


def predict(payload: dict[str, Any]) -> dict[str, Any]:
    physics = simulate_collision(payload)
    model = get_model()
    feats = flatten_features({**payload, "restitution": physics["restitution"]})
    x = torch.tensor(feats, dtype=torch.float32).unsqueeze(0)

    if model is None:
        rng = random.Random(hash(tuple(feats)) % (2**32))
        noise = 0.08
        v1 = {
            "x": physics["v1"]["x"] + rng.uniform(-noise, noise),
            "y": physics["v1"]["y"] + rng.uniform(-noise, noise),
        }
        v2 = {
            "x": physics["v2"]["x"] + rng.uniform(-noise, noise),
            "y": physics["v2"]["y"] + rng.uniform(-noise, noise),
        }
        ke_f = (
            0.5 * payload["m1"] * (v1["x"] ** 2 + v1["y"] ** 2)
            + 0.5 * payload["m2"] * (v2["x"] ** 2 + v2["y"] ** 2)
        )
        return {
            "v1": v1,
            "v2": v2,
            "ke_final": ke_f,
            "source": "baseline_noise",
        }

    with torch.no_grad():
        y = model(x).squeeze(0).tolist()
    v1 = {"x": float(y[0]), "y": float(y[1])}
    v2 = {"x": float(y[2]), "y": float(y[3])}
    ke_f = (
        0.5 * payload["m1"] * (v1["x"] ** 2 + v1["y"] ** 2)
        + 0.5 * payload["m2"] * (v2["x"] ** 2 + v2["y"] ** 2)
    )
    return {
        "v1": v1,
        "v2": v2,
        "ke_final": ke_f,
        "source": "mlp",
    }
