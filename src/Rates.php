<?php

namespace BestChange;

class Rates
{
    private $data = [];

    public function __construct($data)
    {
        $data = explode("\n", $data);
        foreach ($data as $row) {
            $row = iconv('CP1251', 'UTF-8', $row);
            $data = explode(';', $row);
            if (count($data) < 5) {
                continue;
            }
            $rateGive = (float)$data[3];
            $rateReceiver = (float)$data[4];
            if (!$rateGive || !$rateReceiver) {
                continue;
            }
            $rate = $rateReceiver ? $rateGive / $rateReceiver : 0;
            $this->data[$data[0]][$data[1]][$data[2]] = [
                'exchanger_id' => (int)$data[2],
                'rate_give' => $rateGive,
                'rate_receiver' => $rateReceiver,
                'rate' => $rate,
                'reserve' => $data[5],
            ];
        }
        $this->sortRateAscAll();
    }

    public function get()
    {
        return $this->data;
    }

    public function filter($currencyReceiveID = 0, $currencyGiveID = 0)
    {
        if ($currencyReceiveID && $currencyGiveID) {
            return $this->data[$currencyReceiveID][$currencyGiveID];
        }
        return $this->get();
    }

    private function sortRateAsc($a, $b)
    {
        if ($a['rate'] == $b['rate']) {
            return 0;
        }
        return ($a['rate'] < $b['rate']) ? -1 : 1;
    }

    /**
     * Отсортируем все по rate ASC
     * @return $this
     */
    private function sortRateAscAll()
    {
        foreach ($this->data as $currencyReceiveID => $currencyIn) {
            foreach ($currencyIn as $currencyGiveID => $item) {
                uasort($item, [$this, 'sortRateAsc']);
                $this->data[$currencyReceiveID][$currencyGiveID] = $item;
            }
        }
        return $this;
    }
}