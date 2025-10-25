-- SELECT * From funds Where amc_id = 64;

-- Select * FROM funds Where name like '%Parag Parikh Flexi Cap Fund%';
-- Select * FROM funds Where category like '%Hybrid Scheme - Conservative Hybrid Fund%' and amc_id = 64;
-- Select * FROM funds Where category like 'Hybrid Scheme - Dynamic Asset Allocation or Balanced Advantage' and amc_id = 64;


-- Select count(*) From funds;

-- Select count(*) From schemes;

Select * From fund_aum_histories Order by fund_id, start_date;

Select * From schemes;

Select * From funds where id = 18113;

Select * From funds Where amc_id = 20 and category like '%ELSS%';

-- and name like '%ICICI Prudential Long Term Wealth Enhancement Fund%';

Select * From schemes Where amc_id = 20 and id = 142136;

