"""
Train CollisionMLP on synthetic data from physics_core.
Usage:  python train.py --samples 8000 --epochs 40
"""
from __future__ import annotations

import argparse
import random

import torch
import torch.nn as nn
from torch.utils.data import DataLoader, TensorDataset

from model_infer import CollisionMLP
from physics_core import flatten_features, simulate_collision


def generate_batch(n: int, rng: random.Random) -> tuple[torch.Tensor, torch.Tensor]:
    xs: list[list[float]] = []
    ys: list[list[float]] = []
    for _ in range(n):
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
        xs.append(flatten_features(payload))
        ys.append(
            [
                out["v1"]["x"],
                out["v1"]["y"],
                out["v2"]["x"],
                out["v2"]["y"],
            ]
        )
    return torch.tensor(xs, dtype=torch.float32), torch.tensor(ys, dtype=torch.float32)


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--samples", type=int, default=6000)
    ap.add_argument("--epochs", type=int, default=30)
    ap.add_argument("--hidden", type=int, default=64)
    ap.add_argument("--lr", type=float, default=1e-3)
    ap.add_argument("--seed", type=int, default=42)
    ap.add_argument("--out", type=str, default="collision_mlp.pt")
    args = ap.parse_args()

    rng = random.Random(args.seed)
    torch.manual_seed(args.seed)

    x, y = generate_batch(args.samples, rng)
    ds = TensorDataset(x, y)
    loader = DataLoader(ds, batch_size=128, shuffle=True)

    model = CollisionMLP(hidden=args.hidden)
    opt = torch.optim.Adam(model.parameters(), lr=args.lr)
    loss_fn = nn.MSELoss()

    for epoch in range(args.epochs):
        total = 0.0
        for xb, yb in loader:
            opt.zero_grad()
            pred = model(xb)
            loss = loss_fn(pred, yb)
            loss.backward()
            opt.step()
            total += loss.item() * xb.size(0)
        print(f"epoch {epoch + 1} mse {total / len(ds):.6f}")

    torch.save(
        {"state_dict": model.state_dict(), "hidden": args.hidden},
        args.out,
    )
    print("saved", args.out)


if __name__ == "__main__":
    main()
