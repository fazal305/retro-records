<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\AssetRepository;
use App\Security\Session;
use App\Validation\Validator;
use PDOException;

final class AssetController
{
    public const PER_PAGE = 10;

    private AssetRepository $assets;

    public function __construct()
    {
        $this->assets = new AssetRepository();
    }

    public function index(Request $request): Response
    {
        $userId = $request->user['id'];
        $filters = [
            'q' => mb_substr($request->query('q'), 0, 100),
            'environment' => $request->query('environment'),
            'status' => $request->query('status'),
            'sort' => array_key_exists($request->query('sort'), AssetRepository::SORTS) ? $request->query('sort') : 'updated',
        ];
        $page = max(1, (int) $request->query('page', '1'));

        $result = $this->assets->paginate($userId, $filters, $page, self::PER_PAGE);
        $pages = max(1, (int) ceil($result['total'] / self::PER_PAGE));
        if ($page > $pages) {
            return Response::redirect(url('/assets', $filters + ['page' => $pages]));
        }

        return Response::view('assets/index', [
            'title' => 'Server inventory',
            'nav' => 'assets',
            'assets' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => $pages,
            'filters' => $filters,
            'filtered' => $filters['q'] !== '' || in_array($filters['environment'], AssetRepository::ENVIRONMENTS, true)
                || in_array($filters['status'], AssetRepository::STATUSES, true),
            'counts' => $this->assets->statusCounts($userId),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->form('new', ['environment' => 'production', 'status' => 'online']);
    }

    public function store(Request $request): Response
    {
        $userId = $request->user['id'];
        [$data, $errors] = Validator::asset($this->input($request));

        if (!$errors && $this->assets->countOwned($userId) >= AssetRepository::PER_USER_LIMIT) {
            $errors['hostname'] = 'You have reached the limit of ' . AssetRepository::PER_USER_LIMIT . ' servers. Delete some before adding more.';
        }
        if (!isset($errors['hostname']) && $this->assets->hostnameExists($userId, $data['hostname'])) {
            $errors['hostname'] = 'You already have a server with this hostname.';
        }
        if ($errors) {
            return $this->form('new', $data, $errors, 422);
        }

        try {
            $this->assets->create($userId, $data);
        } catch (PDOException $e) {
            return $this->handleWriteError($e, 'new', $data);
        }

        Session::flash('ok', "Server {$data['hostname']} added.");
        return Response::redirect('/assets');
    }

    public function edit(Request $request): Response
    {
        $asset = $this->assets->findOwned($request->params['id'], $request->user['id']);
        return $asset ? $this->form('edit', $asset) : Response::error(404, 'That server does not exist or is not yours.');
    }

    public function update(Request $request): Response
    {
        $id = $request->params['id'];
        $userId = $request->user['id'];
        if ($this->assets->findOwned($id, $userId) === null) {
            return Response::error(404, 'That server does not exist or is not yours.');
        }

        [$data, $errors] = Validator::asset($this->input($request));
        if (!isset($errors['hostname']) && $this->assets->hostnameExists($userId, $data['hostname'], $id)) {
            $errors['hostname'] = 'You already have a server with this hostname.';
        }
        if ($errors) {
            return $this->form('edit', $data + ['id' => $id], $errors, 422);
        }

        try {
            $this->assets->update($id, $userId, $data);
        } catch (PDOException $e) {
            return $this->handleWriteError($e, 'edit', $data + ['id' => $id]);
        }

        Session::flash('ok', "Server {$data['hostname']} updated.");
        return Response::redirect($this->returnTo($request));
    }

    public function confirmDelete(Request $request): Response
    {
        $asset = $this->assets->findOwned($request->params['id'], $request->user['id']);
        if ($asset === null) {
            return Response::error(404, 'That server does not exist or is not yours.');
        }
        return Response::view('assets/delete', ['title' => 'Delete server', 'nav' => 'assets', 'asset' => $asset]);
    }

    public function destroy(Request $request): Response
    {
        $asset = $this->assets->findOwned($request->params['id'], $request->user['id']);
        if ($asset === null || !$this->assets->delete($asset['id'], $request->user['id'])) {
            return Response::error(404, 'That server does not exist or is not yours.');
        }
        Session::flash('ok', "Server {$asset['hostname']} deleted.");
        return Response::redirect($this->returnTo($request));
    }

    private function input(Request $request): array
    {
        $fields = ['hostname', 'ip_address', 'location', 'environment', 'status'];
        $input = array_combine($fields, array_map($request->input(...), $fields));
        $input['notes'] = $request->raw('notes');
        return $input;
    }

    private function form(string $mode, array $asset, array $errors = [], int $status = 200): Response
    {
        return Response::view('assets/form', [
            'title' => $mode === 'new' ? 'Add server' : 'Edit server',
            'nav' => $mode === 'new' ? 'new' : 'assets',
            'mode' => $mode,
            'asset' => $asset,
            'errors' => $errors,
        ], $status);
    }

    private function handleWriteError(PDOException $e, string $mode, array $data): Response
    {
        if ($e->getCode() === '23505') {
            return $this->form($mode, $data, ['hostname' => 'You already have a server with this hostname.'], 422);
        }
        throw $e;
    }

    private function returnTo(Request $request): string
    {
        $to = $request->input('return_to');
        return str_starts_with($to, '/assets') ? $to : '/assets';
    }
}
