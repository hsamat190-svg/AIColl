"""2D circle–circle collision impulse (coefficient of restitution e)."""
from __future__ import annotations

import math
from typing import Any


def ke(m: float, vx: float, vy: float) -> float:
    return 0.5 * m * (vx * vx + vy * vy)


def momentum(m1: float, v1: dict, m2: float, v2: dict) -> dict[str, float]:
    return {
        "x": m1 * v1["x"] + m2 * v2["x"],
        "y": m1 * v1["y"] + m2 * v2["y"],
    }


def simulate_collision(payload: dict[str, Any]) -> dict[str, Any]:
    m1 = float(payload["m1"])
    m2 = float(payload["m2"])
    r1 = float(payload["r1"])
    r2 = float(payload["r2"])
    p1 = payload["p1"]
    p2 = payload["p2"]
    v1 = payload["v1"]
    v2 = payload["v2"]
    e = float(payload.get("restitution", 1.0))
    if payload.get("collision_type") == "elastic":
        e = 1.0

    dx = p2["x"] - p1["x"]
    dy = p2["y"] - p1["y"]
    dist = math.hypot(dx, dy)
    if dist < 1e-9:
        dist = 1e-9
    nx, ny = dx / dist, dy / dist

    v1x, v1y = v1["x"], v1["y"]
    v2x, v2y = v2["x"], v2["y"]

    vn = (v1x - v2x) * nx + (v1y - v2y) * ny
    if vn <= 0:
        v1_out = {"x": v1x, "y": v1y}
        v2_out = {"x": v2x, "y": v2y}
    elif payload.get("collision_type") == "inelastic":
        # Абсолютно неупругий: общая скорость центра масс (одинаковые векторы v1=v2).
        M = m1 + m2
        vcmx = (m1 * v1x + m2 * v2x) / M
        vcmy = (m1 * v1y + m2 * v2y) / M
        v1_out = {"x": vcmx, "y": vcmy}
        v2_out = {"x": vcmx, "y": vcmy}
    else:
        inv_m = 1.0 / m1 + 1.0 / m2
        j = -(1.0 + e) * vn / inv_m
        v1_out = {"x": v1x + j * nx / m1, "y": v1y + j * ny / m1}
        v2_out = {"x": v2x - j * nx / m2, "y": v2y - j * ny / m2}

    ke_i = ke(m1, v1x, v1y) + ke(m2, v2x, v2y)
    ke_f = ke(m1, v1_out["x"], v1_out["y"]) + ke(m2, v2_out["x"], v2_out["y"])
    pi = momentum(m1, v1, m2, v2)
    pf = momentum(m1, v1_out, m2, v2_out)

    damage_estimate = max(0.0, (1.0 - e) * ke_i * 0.02)

    return {
        "v1": v1_out,
        "v2": v2_out,
        "ke_initial": ke_i,
        "ke_final": ke_f,
        "momentum_initial": pi,
        "momentum_final": pf,
        "collision_type": payload.get("collision_type", "elastic"),
        "restitution": e,
        "material": payload.get("material", "custom"),
        "damage_estimate": damage_estimate,
    }


def flatten_features(p: dict[str, Any]) -> list[float]:
    return [
        p["m1"],
        p["m2"],
        p["r1"],
        p["r2"],
        p["p1"]["x"],
        p["p1"]["y"],
        p["p2"]["x"],
        p["p2"]["y"],
        p["v1"]["x"],
        p["v1"]["y"],
        p["v2"]["x"],
        p["v2"]["y"],
        float(p.get("restitution", 1.0)),
    ]
