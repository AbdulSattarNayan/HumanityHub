<?php
class FPDF_Polygon extends FPDF {
    function Polygon($points, $style='D') {
        // Check if the number of points is even (must be pairs of x, y coordinates)
        if (count($points) % 2 != 0) {
            $this->Error('Polygon: Number of coordinates must be even (x, y pairs).');
        }

        // Start the path
        $this->_out('q'); // Save graphics state
        $this->_out(sprintf('%.2F %.2F m', $points[0] * $this->k, ($this->h - $points[1]) * $this->k)); // Move to first point

        // Draw lines to each subsequent point
        for ($i = 2; $i < count($points); $i += 2) {
            $this->_out(sprintf('%.2F %.2F l', $points[$i] * $this->k, ($this->h - $points[$i + 1]) * $this->k));
        }

        // Close the path (connect back to the first point)
        $this->_out('h');

        // Draw or fill the polygon based on style
        if ($style == 'F') {
            $this->_out('f'); // Fill
        } elseif ($style == 'FD' || $style == 'DF') {
            $this->_out('B'); // Fill and stroke
        } else {
            $this->_out('S'); // Stroke
        }

        $this->_out('Q'); // Restore graphics state
    }
}