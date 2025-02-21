<?php

namespace LoadPartner\FmcsaSaferApi;

use Illuminate\Support\Facades\Http;
use LoadPartner\FmcsaSaferApi\Exceptions\FmcsaError;
use LoadPartner\FmcsaSaferApi\Exceptions\FmcsaNotFoundError;

class FmcsaSafer
{

    const BASE_URL = 'https://mobile.fmcsa.dot.gov/qc/services/';

    public function __construct(protected string $apiKey, protected string $baseUrl = self::BASE_URL)
    {
    }

    public function getFullReport(string $dotNumber) : array
    {
        $report = [];

        $searchResult = $this->searchCarrierDOT($dotNumber);

        if (empty($searchResult['content'])) {
            throw new FmcsaError('Failed to get data from FMCSa API - Empty result');
        }

        $report['general'] = $searchResult['content'];
        $report['basics'] = $this->getCarrierBasics($dotNumber)['content'] ?? [];
        $report['cargo-carried'] = $this->getCarrierCargoCarried($dotNumber)['content'] ?? [];
        $report['operation-classification'] = $this->getCarrierOperationClassification($dotNumber)['content'] ?? [];
        $report['oos'] = $this->getCarrierOos($dotNumber)['content'] ?? [];
        $report['docket-numbers'] = $this->getCarrierDocketNumbers($dotNumber)['content'] ?? [];
        $report['authority'] = $this->getCarrierAuthority($dotNumber)['content'] ?? [];
        $report['full-report'] = 'true';

        return $report;
    }

    public function searchCarrierName($carrierName, int $limit = 10)
    {
        $report = [];

        $searchResult = $this->getWithKey('carriers/name/' . $carrierName, ['size' => $limit]);

        foreach($searchResult['content'] as $carrier) {
            $report[] = [
                'general' => $carrier,
                'full-report' => 'false',
            ];
        }

        return $report;
    }

    protected function searchCarrierDOT($dotNumber)
    {
        return $this
            ->getWithKey('carriers/' . $dotNumber);
    }

    protected function searchCarrierMC($mcNumber)
    {
        return $this
            ->getWithKey('carriers/docket-number/' . $mcNumber);
    }

    protected function getCarrierMC($dotNumber)
    {
        return $this
            ->getWithKey('carriers/' . $dotNumber . '/mc-numbers');
    }

    protected function getCarrierBasics($dotNumber)
    {
        return $this
            ->getWithKey('carriers/' . $dotNumber . '/basics');
    }

    protected function getCarrierCargoCarried($dotNumber)
    {
        return $this
            ->getWithKey('carriers/' . $dotNumber . '/cargo-carried');
    }

    protected function getCarrierOperationClassification($dotNumber)
    {
        return $this
            ->getWithKey('carriers/' . $dotNumber . '/operation-classification');
    }

    protected function getCarrierOos($dotNumber)
    {
        return $this
            ->getWithKey('carriers/' . $dotNumber . '/oos');
    }

    protected function getCarrierDocketNumbers($dotNumber)
    {
        return $this
            ->getWithKey('carriers/' . $dotNumber . '/docket-numbers');
    }

    protected function getCarrierAuthority($dotNumber)
    {
        return $this
            ->getWithKey('carriers/' . $dotNumber . '/authority');
    }

    protected function getWithKey(string $path, array $params = []) : ?array
    {
        $client = new \GuzzleHttp\Client();
        $result = $client->request('GET', $this->baseUrl . $path, [
            'query' => [
                'webKey' => $this->apiKey,
                ...$params
            ]
        ]);
        
        $resultJson = json_decode($result->getBody()->getContents(), true);

        if ($result->getStatusCode() === 404 || $resultJson['content'] === null) {
            throw new FmcsaNotFoundError('Carrier not found');
        }

        if ($result->getStatusCode() === 200) {
            return $resultJson;
        }

        throw new FmcsaError('Failed to get data from FMCSa API - Unexpected server response');
    }
}