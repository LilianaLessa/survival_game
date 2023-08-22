<?php

declare(strict_types = 1);

namespace App\System\Helpers;

class PerlinNoiseGenerator
{
    /**
     *  Hash lookup table as defined by Ken Perlin.
     *  This is a randomly arranged array of all numbers from 0-255 inclusive.
     *
     * @var int[]
     */
    private static array $permutation = [
        151, 160, 137, 91, 90, 15, 131, 13, 201, 95, 96, 53, 194, 233, 7, 225,
        140, 36, 103, 30, 69, 142, 8, 99, 37, 240, 21, 10, 23, 190, 6, 148, 247,
        120, 234, 75, 0, 26, 197, 62, 94, 252, 219, 203, 117, 35, 11, 32, 57, 177,
        33, 88, 237, 149, 56, 87, 174, 20, 125, 136, 171, 168, 68, 175, 74, 165,
        71, 134, 139, 48, 27, 166, 77, 146, 158, 231, 83, 111, 229, 122, 60, 211,
        133, 230, 220, 105, 92, 41, 55, 46, 245, 40, 244, 102, 143, 54, 65, 25, 63,
        161, 1, 216, 80, 73, 209, 76, 132, 187, 208, 89, 18, 169, 200, 196, 135,
        130, 116, 188, 159, 86, 164, 100, 109, 198, 173, 186, 3, 64, 52, 217, 226,
        250, 124, 123, 5, 202, 38, 147, 118, 126, 255, 82, 85, 212, 207, 206, 59,
        227, 47, 16, 58, 17, 182, 189, 28, 42, 223, 183, 170, 213, 119, 248, 152,
        2, 44, 154, 163, 70, 221, 153, 101, 155, 167, 43, 172, 9, 129, 22, 39, 253,
        19, 98, 108, 110, 79, 113, 224, 232, 178, 185, 112, 104, 218, 246, 97,
        228, 251, 34, 242, 193, 238, 210, 144, 12, 191, 179, 162, 241, 81, 51,
        145, 235, 249, 14, 239, 107, 49, 192, 214, 31, 181, 199, 106, 157, 184,
        84, 204, 176, 115, 121, 50, 45, 127, 4, 150, 254, 138, 236, 205, 93,
        222, 114, 67, 29, 24, 72, 243, 141, 128, 195, 78, 66, 215, 61, 156, 180
    ];

    /**
     * @var int[]
     */
    private array $p;

    public function __construct(
        private readonly int $seed = - 1,
    ) {
        $this->p = [];
        for ($x = 0;$x < 512;$x++)
        {
            $this->p[$x] = self::$permutation[$x % 256];
        }
    }

    /**
     * @return float[][]
     */
    public function generateMap2D(
        int $width,
        int $height,
        float $scale,
        Point2D $offset,
        PerlinNoiseWave ...$waves
    ): array {
        $noiseMap = [];

        for ($x = 0; $x < $width; ++$x)
        {
            for ($y = 0; $y < $height; ++$y)
            {
                $samplePosX = (float) $x * $scale + $offset->getX();
                $samplePosY = (float) $y * $scale + $offset->getY();

                $normalization = 0.0;
                // loop through each wave
                foreach ($waves as $wave)
                {
                    // sample the perlin noise taking into consideration amplitude and frequency
                    $noiseMap[$x][$y] = $noiseMap[$x][$y] ?? 0;
                    $noiseMap[$x][$y] +=
                        $wave->amplitude * $this->perlin(
                            $samplePosX * $wave->frequency + $wave->seed,
                            $samplePosY * $wave->frequency + $wave->seed,
                            0
                        );
                    $normalization += $wave->amplitude;
                }

                // normalize the value
                $noiseMap[$x][$y] /= $normalization;
            }
        }

        return $noiseMap;
    }

    private function perlin(float $x, float $y, float $z): float
    {
        // If we have any repeat on, change the coordinates to their "local" repetitions
        if ($this->seed > 0)
        {
            $x = $x % $this->seed;
            $y = $y % $this->seed;
            $z = $z % $this->seed;
        }

        // Calculate the "unit cube" that the point asked will be located in
        // The left bound is ( |_x_|,|_y_|,|_z_| ) and the right bound is that
        // plus 1.  Next we calculate the location (from 0.0 to 1.0) in that cube.
        // We also fade the location to smooth the result.
        $xi = (int)$x & 255;
        $yi = (int)$y & 255;
        $zi = (int)$z & 255;
        $xf = (float)$x - (int)$x;
        $yf = (float)$y - (int)$y;
        $zf = (float)$z - (int)$z;
        $u = (float)$this->fade($xf);
        $v = (float)$this->fade($yf);
        $w = (float)$this->fade($zf);

        $aaa = (int)$this->p[$this->p[$this->p[$xi] + $yi] + $zi];
        $aba = (int)$this->p[$this->p[$this->p[$xi] + $this->inc($yi) ] + $zi];
        $aab = (int)$this->p[$this->p[$this->p[$xi] + $yi] + $this->inc($zi) ];
        $abb = (int)$this->p[$this->p[$this->p[$xi] + $this->inc($yi) ] + $this->inc($zi) ];
        $baa = (int)$this->p[$this->p[$this->p[$this->inc($xi) ] + $yi] + $zi];
        $bba = (int)$this->p[$this->p[$this->p[$this->inc($xi) ] + $this->inc($yi) ] + $zi];
        $bab = (int)$this->p[$this->p[$this->p[$this->inc($xi) ] + $yi] + $this->inc($zi) ];
        $bbb = (int)$this->p[$this->p[$this->p[$this->inc($xi) ] + $this->inc($yi) ] + $this->inc($zi) ];

        // The gradient function calculates the dot product between a pseudorandom
        // gradient vector and the vector from the input coordinate to the 8
        // surrounding points in its unit cube.
        // This is all then lerped together as a sort of weighted average based on the faded (u,v,w)
        // values we made earlier.
        //double x1, x2, y1, y2;
        $x1 = $this->lerp(
            $this->grad($aaa, $xf, $yf, $zf),
            $this->grad($baa, $xf - 1, $yf, $zf),
            $u
        );
        $x2 = $this->lerp(
            $this->grad($aba, $xf, $yf - 1, $zf),
            $this->grad($bba, $xf - 1, $yf - 1, $zf),
            $u
        );
        $y1 = $this->lerp($x1, $x2, $v);

        $x1 = $this->lerp(
            $this->grad($aab, $xf, $yf, $zf - 1),
            $this->grad($bab, $xf - 1, $yf, $zf - 1),
            $u
        );
        $x2 = $this->lerp(
            $this->grad($abb, $xf, $yf - 1, $zf - 1),
            $this->grad($bbb, $xf - 1, $yf - 1, $zf - 1),
            $u
        );
        $y2 = $this->lerp($x1, $x2, $v);

        // For convenience, we bound it to 0 - 1 (theoretical min/max before is -1 - 1)
        return ($this->lerp($y1, $y2, $w) + 1) / 2;
    }

    private function inc(int $num): int
    {
        $num++;
        if ($this->seed > 0)
        {
            $num %= $this->seed;
        }

        return $num;
    }

    /**
     * Fade function as defined by Ken Perlin. This eases coordinate values
     * so that they will ease towards integral values.  This ends up smoothing
     * the final output.
     */
    private function fade(float $t): float
    {
        return $t * $t * $t * ($t * ($t * 6 - 15) + 10); // 6t^5 - 15t^4 + 10t^3

    }

    private function grad(int $hash, float $x, float $y, float $z): float
    {
        return match($hash & 0xF) {
            0x0 =>  $x + $y,
            0x1 => -$x + $y,
            0x2 =>  $x - $y,
            0x3 => -$x - $y,
            0x4 =>  $x + $z,
            0x5 => -$x + $z,
            0x6 =>  $x - $z,
            0x7 => -$x - $z,
            0x8 =>  $y + $z,
            0x9 => -$y + $z,
            0xA =>  $y - $z,
            0xB => -$y - $z,
            0xC =>  $y + $x,
            0xD => -$y + $z,
            0xE =>  $y - $x,
            0xF => -$y - $z,
            default => 0, // never happens
        };
    }

    private function lerp(float $a, float $b, float $x): float
    {
        return $a + $x * ($b - $a);
    }
}
