<?php
namespace Meanify\LaravelObfuscator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Meanify\LaravelObfuscator\Support\IdObfuscator;

class ObfuscatorCommand extends Command
{
    protected $signature = 'meanify:obfuscator 
                            {--encode : Generate obfuscated ID}
                            {--decode : Decode obfuscated ID}
                            {--list : Show last 20 failures by default}
                            {--count : number of failures to list}
                            {--clear : delete obfuscated failures}';

    protected $description = 'Handle obfuscator failures and generate obfuscated ids';

    /**
     * @return int
     */
    public function handle(): int
    {
        if ($this->option('encode'))
        {
            $id    = $this->ask('Type ID to generate obfuscated value');
            $model = $this->ask('Type Model\'s path from ID');

            return IdObfuscator::encode($id, $model);
        }
        else if ($this->option('decode'))
        {
            $id    = $this->ask('Type ID to get decoded value');
            $model = $this->ask('Type Model\'s path from ID');

            return IdObfuscator::decode($id, $model);
        }
        else
        {
            $table = Config::get('meanify-laravel-obfuscator.table', 'obfuscator_failures');

            if (!DB::getSchemaBuilder()->hasTable($table))
            {
                $this->error("Table '{$table}' not found.");
                return 1;
            }

            if ($this->option('list'))
            {
                $count = $this->option('count') ?? 20;

                $failures = DB::table($table)->orderByDesc('created_at')->limit($count)->get();

                if ($failures->isEmpty()) {
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
                return 0;
            }
            else if ($this->option('clear'))
            {
                $count = DB::table($table)->delete();
                $this->info("{$count} failures deleted.");
                return 0;
            }
        }

        $this->info('Type --generate to obfuscate ID, --list to display last failures or --clear to delete all data.');
        return 0;
    }
}
