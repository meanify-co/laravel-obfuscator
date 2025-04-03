<?php
namespace Meanify\LaravelObfuscator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Meanify\LaravelObfuscator\Support\IdObfuscator;
use function Laravel\Prompts\form;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;

class ObfuscatorCommand extends Command
{
    protected $signature = 'meanify:obfuscator 
                            {--id= : Original ID to encode or obfuscated ID to decode}
                            {--model= : Model from ID value}
                            {--list : Show last 20 failures by default}
                            {--count= : number of failures to list}
                            {--clear : delete obfuscated failures}';

    protected $description = 'Handle obfuscator failures and generate obfuscated ids';

    /**
     * @return int
     */
    public function handle(): int
    {
        $action = select(
            label: 'What do you wanna execute?',
            options: ['real' => 'Insert real IDs to obfuscate', 'obfuscated' => 'Insert obfuscated IDs to validate real ID', 'failures' => 'Show failures'],
            default: 'real',
        );

        if($action !== 'failures')
        {

            $responses = form()
                ->text('Insert IDs (separated by comman)',
                    required: true,
                    name: 'id'
                )
                ->text('Type model path from IDs', required: true, name: 'model', placeholder: 'E.g.: App\Models\User', default: 'App\Models\User')
                ->submit();

            $ids   = $responses['id'];
            $model = $responses['model'];
            $rows   = [];

            foreach (explode(',', $ids) as $id)
            {
                $result = $action == 'real' ? IdObfuscator::encode($id, $model) : IdObfuscator::decode($id, $model);
                $rows[] = [
                    $id,
                    $model,
                    $result
                ];
            }

            $this->table(
                $action == 'real' ? ['Real ID', 'Model', 'Obfuscated ID'] : ['Obfuscated ID', 'Model', 'Real ID'],
                $rows
            );

            return 0;
        }
        else
        {
            $table = Config::get('meanify-laravel-obfuscator.table', 'obfuscator_failures');

            if (!DB::getSchemaBuilder()->hasTable($table))
            {
                $this->error("Table '{$table}' not found.");
                return 1;
            }
            
            $responses = form()
                ->text('Define number of rows to display list',
                    required: true,
                    name: 'count',
                    default: 20
                )
                ->submit();
            
            $count = $responses['count'];

            $failures = DB::table($table)->orderByDesc('created_at')->limit($count)->get();

            if ($failures->isEmpty())
            {
                $this->info('No failures found.');
                return 0;
            }

            $this->table(
                ['ID', 'Model', 'Input', 'Reason', 'Created At'],
                $failures->map(fn ($f) => [
                    $f->id,
                    $f->model,
                    $f->input,
                    Str::limit($f->reason, 50),
                    $f->created_at,
                ])
            );

            $responses = form()
                ->confirm('Do you wanna delete all failures?', name: 'confirm', default: false)
                ->submit();

            if($responses['confirm'])
            {
                $count = DB::table($table)->delete();
                $this->info("{$count} failures deleted.");
                return 0;
            }

            return 0;
        }
    }
}
