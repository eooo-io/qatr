<?php

it('returns a successful health check response', function () {
    $response = $this->getJson('/api/v1/health');

    $response
        ->assertStatus(200)
        ->assertExactJson([
            'status' => 'ok',
            'version' => '0.1.0',
        ]);
});
