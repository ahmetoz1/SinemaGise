DELIMITER //

CREATE TRIGGER bilet_silme_sonrasi_koltuk_guncelle
AFTER DELETE ON bilet
FOR EACH ROW
BEGIN
    -- Silinen biletin koltuğunu boşalt
    UPDATE koltuk 
    SET durum = 'bos' 
    WHERE koltuk_id = OLD.koltuk_id;
END//

DELIMITER ; 