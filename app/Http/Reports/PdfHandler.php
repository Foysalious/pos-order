<?php namespace App\Http\Reports;

use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Throwable;
use App\Services\FileManagers\CdnFileManager;
use Illuminate\Http\JsonResponse;

class PdfHandler extends Handler
{
    use CdnFileManager;

    /** @var PDF $pdf */
    private $pdf;
    private $downloadFormat = "pdf";

    public function __construct()
    {
        $this->pdf = app('dompdf.wrapper');
    }

    public function create()
    {
        $this->pdf->loadView($this->viewFileName, $this->data);
        return $this;
    }

    /**
     * @param bool $mPdf
     * @return Response|string
     * @throws MpdfException
     * @throws Throwable
     */
    public function download($mPdf = false)
    {
        if ($mPdf) {
            $mPDF = $this->getMpdf();
            $mPDF->simpleTables = true;
            $mPDF->packTableData = true;
            $mPDF->shrink_tables_to_fit = 1;
            $keep_table_proportions = TRUE;
            $data = view($this->viewFileName, $this->data)->render();
            $mPDF->WriteHTML("$data", HTMLParserMode::DEFAULT_MODE);
            return $mPDF->Output("$this->filename.$this->downloadFormat", "d");
        }
        $this->create();

        return $this->pdf->download("$this->filename.$this->downloadFormat");
    }

    /**
     * @throws MpdfException
     */
    private function getMpdf()
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        return new Mpdf([
            'mode' => 'utf-8',
            'tempDir' => storage_path('app/temp'),
            'fontDir' => array_merge($fontDirs, [
                storage_path('/fonts'),
            ]), 'fontdata' => $fontData + [
                    'kalpurush' => [
                        'R' => 'Siyamrupali.ttf', 'I' => 'Siyamrupali.ttf', 'useOTL' => 0xFF, 'useKashida' => 75,
                    ]
                ], 'default_font' => 'kalpurush'
        ]);

    }

    /**
     * @throws MpdfException
     * @throws Throwable
     */

    public function save()
    {
        $mPDF = $this->getMpdf();
        $mPDF->simpleTables = true;
        $mPDF->packTableData = true;
        $mPDF->setFooter('{PAGENO}');
        $mPDF->shrink_tables_to_fit = 1;
        $data = view($this->viewFileName, $this->data)->render();
        $stylesheet = file_get_contents('assets/CSS/style.css');
        $mPDF->WriteHTML($stylesheet, 1);
        $mPDF->WriteHTML("$data", HTMLParserMode::DEFAULT_MODE);

        $folder = $this->folder ?: 'invoices/pdf/';
        $time = time();
        $file = $this->filename . "_$time." . $this->downloadFormat;
        $path = public_path() . '/' . $file;
        $mPDF->Output($path, "F");
        $cdn = $this->saveFileToCDN($path, $folder, $file);
        File::delete($path);
        return $cdn;
    }

    protected function getViewPath()
    {
        return $this->viewPath ?: "reports.pdfs.";
    }


}
