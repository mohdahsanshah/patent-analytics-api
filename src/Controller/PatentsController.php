<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\TableRegistry;
use Cake\Http\Exception\BadRequestException;
use Cake\Cache\Cache;


class PatentsController extends AppController
{
    public function summary()
    {
        $this->request->allowMethod(['get']);
        $this->autoRender = false;

        // Check Cache First
        $cachedData = Cache::read('summary_data', 'summary_cache');

        if ($cachedData) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode($cachedData));
        }

        $patents = TableRegistry::getTableLocator()->get('Patents');
        $connection = $patents->getConnection();

        // Run SQL Only If Not Cached
        $sql = "
            SELECT
                COUNT(*) AS total_patents,
                AVG(publication_year) AS mean_year,
                MIN(publication_year) AS min_year,
                MAX(publication_year) AS max_year,
                STDDEV(publication_year) AS stddev_year,
                PERCENTILE_CONT(0.5)
                    WITHIN GROUP (ORDER BY publication_year) AS median_year
            FROM patents
            WHERE publication_year IS NOT NULL
        ";

        $statement = $connection->execute($sql);
        $result = $statement->fetch('assoc');

        $yearDistribution = $connection->execute("
            SELECT publication_year, COUNT(*) as total
            FROM patents
            GROUP BY publication_year
            ORDER BY publication_year DESC
            LIMIT 10
        ")->fetchAll('assoc');

        $topAssignees = $connection->execute("
            SELECT assignee, COUNT(*) as total
            FROM patents
            WHERE assignee IS NOT NULL
            GROUP BY assignee
            ORDER BY total DESC
            LIMIT 5
        ")->fetchAll('assoc');

        $result = [
            'total_patents' => (int)$result['total_patents'],
            'mean_year' => (float)$result['mean_year'],
            'min_year' => (int)$result['min_year'],
            'max_year' => (int)$result['max_year'],
            'stddev_year' => (float)$result['stddev_year'],
            'median_year' => (float)$result['median_year'],
        ];

        $responseData = [
            'success' => true,
            'summary' => $result,
            'year_distribution' => $yearDistribution,
            'top_assignees' => $topAssignees
        ];

        // Store in Cache (10 minutes)
        Cache::write('summary_data', $responseData, 'summary_cache');

        $this->response = $this->response->withType('application/json')
            ->withStringBody(json_encode($responseData));
    }

    public function query()
    {
        $this->request->allowMethod(['get']);
        $this->autoRender = false;

        $year = $this->request->getQuery('year');

        if (!$year || !is_numeric($year)) {
            return $this->response
                ->withStatus(400)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Invalid or missing year parameter'
                ]));
        }

        $year = (int)$year; // prevention, convert to interger

        try {
            //code...
            $patents = TableRegistry::getTableLocator()->get('Patents');
    
            $year = $this->request->getQuery('year');
            $assignee = $this->request->getQuery('assignee');
    
            $query = $patents->find();
    
            if ($year !== null) {
                if (!is_numeric($year)) {
                    throw new BadRequestException('Year must be numeric.');
                }
    
                $query->where(['publication_year' => (int)$year]);
            }
    
            if ($assignee !== null) {
                $query->where(['assignee ILIKE' => "%$assignee%"]);
            }
    
            $results = $query->limit(50)->toArray();
    
            $this->response = $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'count' => count($results),
                    'data' => $results
                ]));
        } catch (\Throwable $th) {
            //throw $th;
            return $this->response
                ->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Internal server error'
                ]));
        }
    }

    public function correlation()
    {
        $this->request->allowMethod(['get']);
        $this->autoRender = false;

        $patents = \Cake\ORM\TableRegistry::getTableLocator()->get('Patents');
        $connection = $patents->getConnection();

        $sql = "
        SELECT CORR(
            EXTRACT(YEAR FROM filing_creation_date),
            publication_year
        ) AS correlation_value
        FROM patents
        WHERE filing_creation_date IS NOT NULL
        AND publication_year IS NOT NULL
        ";

        $statement = $connection->execute($sql);
        $result = $statement->fetch('assoc');

        $correlation = isset($result['correlation_value'])
            ? (float)$result['correlation_value']
            : null;

        $this->response = $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'correlation_between_filing_and_publication_year' => $correlation
            ]));
    }
}
