UPDATE vl_samples_worksheetcredentials SET stage = 'has_results' WHERE id IN (SELECT worksheetID FROM vl_results_roche) OR id IN (SELECT worksheetID FROM vl_results_abbott);