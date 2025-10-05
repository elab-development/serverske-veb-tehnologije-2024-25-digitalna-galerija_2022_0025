<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Artwork;
use App\Models\Image;

class UserController extends Controller
{
    // Brisanje korisnika i svega njegovog---njegovih radova i slika u jednoj transakciji
    public function obrisiKorisnika($id)
    {
        DB::beginTransaction();

        try {
            // Pronađi korisnika
            $user = User::find($id);
            if (!$user) {
                return response()->json(['greska' => 'Korisnik nije pronađen.'], 404);
            }

            // Pronađi sve radove tog korisnika
            $radovi = Artwork::where('user_id', $id)->get();
            // Brise slike povezane sa radovima
            foreach ($radovi as $rad) {
                Image::where('artwork_id', $rad->id)->delete();
            }
            // Briseradove
            Artwork::where('user_id', $id)->delete();
            // Obrisi samog korisnika
            $user->delete();

            DB::commit();

            return response()->json(['poruka' => 'Korisnik i svi njegovi radovi/slike su uspešno obrisani.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['greska' => 'Došlo je do greške: ' . $e->getMessage()], 500);
        }
    }
}
