<?php
// Paginate
// sort range and return distance
// 
 public function listProductSaleHome(SearchRequest $searchRequest)
    {
        $pageSize = $searchRequest->pageSize();
        $page = $searchRequest->page();
        $params = [
            'index' => $this->name,
            'body' => [
                'size' => $pageSize,
                'from' => ($page - 1) * $pageSize,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['product_enable' => true]],
                            ['match' => ['is_best_sale' => true]],
                        ],

                    ]
                ],
                'sort' => [
                    '_geo_distance' => [
                        'shop_location' => [
                            'lon' => $searchRequest->longGPS(),
                            'lat' => $searchRequest->latGPS(),
                        ],
                        'order' => 'asc',
                        'mode' => 'min',
                        'unit' => 'km',
                    ],
                    'product_id' => 'desc',
                ],
            ]
        ];
        if ($searchRequest->longGPS() > 0 && $searchRequest->longGPS() > 0) {
            $range = $searchRequest->rangeGPS() . 'km';
            $params['body']['query']['bool']['filter'] = [
                'geo_distance' => [
                    'distance' => $range,
                    'shop_location' => [
                        'lon' => $searchRequest->longGPS(),
                        'lat' => $searchRequest->latGPS()
                    ],
                ],
            ];

        }

        $data = $this->getClient()->search($params);
        if ($data) {
            $data = fn_get_data_from_source_elasticsearch($data, false, true);
            $data = array_merge([
                'currentPage' => $page,
                'pageSize' => $pageSize,
            ], $data);
        }
        return $data ?? [];
    }
