<?php

namespace App\Services;

/**
 * Локальный расчёт столкновения двух шаров (как ai-service/physics_core.simulate_collision),
 * если внешний Physics+AI сервис недоступен — эксперимент и история всё равно сохраняются.
 */
final class LocalCollisionPhysics
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function simulate(array $payload): array
    {
        $m1 = (float) $payload['m1'];
        $m2 = (float) $payload['m2'];
        $p1 = $payload['p1'];
        $p2 = $payload['p2'];
        $v1 = $payload['v1'];
        $v2 = $payload['v2'];
        $e = (float) ($payload['restitution'] ?? 1.0);
        if (($payload['collision_type'] ?? '') === 'elastic') {
            $e = 1.0;
        }

        $dx = (float) $p2['x'] - (float) $p1['x'];
        $dy = (float) $p2['y'] - (float) $p1['y'];
        $dist = hypot($dx, $dy);
        if ($dist < 1e-9) {
            $dist = 1e-9;
        }
        $nx = $dx / $dist;
        $ny = $dy / $dist;

        $v1x = (float) $v1['x'];
        $v1y = (float) $v1['y'];
        $v2x = (float) $v2['x'];
        $v2y = (float) $v2['y'];

        $vn = ($v1x - $v2x) * $nx + ($v1y - $v2y) * $ny;

        if ($vn <= 0) {
            $v1Out = ['x' => $v1x, 'y' => $v1y];
            $v2Out = ['x' => $v2x, 'y' => $v2y];
        } elseif (($payload['collision_type'] ?? '') === 'inelastic') {
            $mSum = $m1 + $m2;
            $vcmx = ($m1 * $v1x + $m2 * $v2x) / $mSum;
            $vcmy = ($m1 * $v1y + $m2 * $v2y) / $mSum;
            $v1Out = ['x' => $vcmx, 'y' => $vcmy];
            $v2Out = ['x' => $vcmx, 'y' => $vcmy];
        } else {
            $invM = 1.0 / $m1 + 1.0 / $m2;
            $j = -(1.0 + $e) * $vn / $invM;
            $v1Out = ['x' => $v1x + $j * $nx / $m1, 'y' => $v1y + $j * $ny / $m1];
            $v2Out = ['x' => $v2x - $j * $nx / $m2, 'y' => $v2y - $j * $ny / $m2];
        }

        $keI = self::ke($m1, $v1x, $v1y) + self::ke($m2, $v2x, $v2y);
        $keF = self::ke($m1, $v1Out['x'], $v1Out['y']) + self::ke($m2, $v2Out['x'], $v2Out['y']);
        $pi = self::momentum($m1, $v1, $m2, $v2);
        $pf = self::momentum($m1, $v1Out, $m2, $v2Out);

        $damage = max(0.0, (1.0 - $e) * $keI * 0.02);

        return [
            'v1' => $v1Out,
            'v2' => $v2Out,
            'ke_initial' => $keI,
            'ke_final' => $keF,
            'momentum_initial' => $pi,
            'momentum_final' => $pf,
            'collision_type' => $payload['collision_type'] ?? 'elastic',
            'restitution' => $e,
            'material' => $payload['material'] ?? 'custom',
            'damage_estimate' => $damage,
            'source' => 'laravel_local',
        ];
    }

    private static function ke(float $m, float $vx, float $vy): float
    {
        return 0.5 * $m * ($vx * $vx + $vy * $vy);
    }

    /**
     * @param  array{x: float|int, y: float|int}  $v1
     * @param  array{x: float|int, y: float|int}  $v2
     * @return array{x: float, y: float}
     */
    private static function momentum(float $m1, array $v1, float $m2, array $v2): array
    {
        return [
            'x' => $m1 * (float) $v1['x'] + $m2 * (float) $v2['x'],
            'y' => $m1 * (float) $v1['y'] + $m2 * (float) $v2['y'],
        ];
    }
}
