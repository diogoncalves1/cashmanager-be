#!/usr/bin/env python3
import yfinance as yf
import sys
from datetime import datetime
import json
import pandas as pd
import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin

def get_dividend_object(t):
    actions = t.actions
    
    divs = actions[actions["Dividends"] > 0]

    if len(divs) == 0:
        return None 

    last = divs.iloc[-1]

    ex_dividend_date = last.name.date()
    dividend_value = float(last["Dividends"])

    info = t.get_info()
    dividend_yield = info.get("dividendYield")  

    calendar = t.calendar
    try:
        dividend_date = calendar.loc["Dividend Date"][0].date()
    except:
        dividend_date = ex_dividend_date

    freq = None
    div_dates = divs.index.to_list()

    if len(div_dates) >= 2:
        delta = div_dates[-1] - div_dates[-2]
        days = delta.days
        if 25 <= days <= 35:
            freq = "Monthly"
        elif 80 <= days <= 100:
            freq = "Quarterly"
        elif 170 <= days <= 200:
            freq = "Semiannual"
        elif 350 <= days <= 380:
            freq = "Yearly"

    if freq is None:
        freq = info.get("payoutFrequency", None)

    dividend_rate = info.get("dividendRate")  # soma dos últimos 12 meses

    if dividend_rate and freq != None:
        if freq == "Monthly":
            dividend_share = dividend_rate / 12
        elif freq == "Quarterly":
            dividend_share = dividend_rate / 4
        elif freq == "Semiannual":
            dividend_share = dividend_rate / 2
        else:
            dividend_share = dividend_rate
    else:
        dividend_share = dividend_value  # fallback simples

    try:
        dividend_ratio = dividend_value / dividend_share
    except:
        dividend_ratio = None

    obj = {
        "dividendDate": str(dividend_date),
        "dividendRatio": dividend_ratio,
        "dividendShare": dividend_share,
        "dividendYield": dividend_yield,
        "exDividendDate": str(ex_dividend_date),
        "payoutFrequency": freq,
        "dividendValue": dividend_value
    }

    return obj

def infer_period(dates):
    """Inferir frequência dos dividendos a partir das datas."""
    if len(dates) < 2:
        return None

    delta = (dates[-1] - dates[-2]).days

    if 25 <= delta <= 35:
        return "Monthly"
    elif 80 <= delta <= 100:
        return "Quarterly"
    elif 170 <= delta <= 200:
        return "Semiannual"
    elif 350 <= delta <= 380:
        return "Annual"

    return None
 
def get_dividend_history(t):
    actions = t.actions
    divs = actions[actions["Dividends"] > 0]

    if len(divs) == 0:
        return []

    info = t.get_info()
    currency = info.get("currency", None)

    dates = divs.index.to_list()
    period = infer_period(dates)

    try:
        pay_date_yf = t.calendar.loc["Dividend Date"][0].date()
    except:
        pay_date_yf = None

    results = []

    for i in range(len(divs)):
        ex_date = divs.index[i].date()
        value = float(divs.iloc[i]["Dividends"])

        if pay_date_yf:
            payment_date = pay_date_yf
        else:
            payment_date = ex_date  # fallback simples

        declaration_date = pd.Timestamp(ex_date) - pd.Timedelta(days=20)
        declaration_date = declaration_date.date()

        record_date = ex_date

        results.append({
            "date": str(ex_date),
            "declarationDate": str(declaration_date),
            "recordDate": str(record_date),
            "paymentDate": str(payment_date),
            "period": period,
            "value": value,
            "unadjustedValue": value,  # igual ao valor sem ajustes
            "currency": currency
        })

    return results

symbol = sys.argv[1] if len(sys.argv) > 1 else 0

if not symbol:
   print(0)

current_year = datetime.now().year

ticker = yf.Ticker(symbol)

info = ticker.get_info()
history = ticker.history(period="5d") 

df = ticker.history(start=f"{current_year}-01-01")

if not df.empty:
    price_start_year = round(df["Open"].iloc[0], 2)
else:
    price_start_year = None

last_days = 20

df = ticker.history(period=f"{last_days}d")

price_last_days_history_json = []

for date, row in df.iterrows():
    price_last_days_history_json.append({
        "date": date.strftime("%Y-%m-%d"),
        "open": round(row["Open"], 2),
        "high": round(row["High"], 2),
        "low": round(row["Low"], 2),
        "close": round(row["Close"], 2),
        "volume": int(row["Volume"])
    })

price_last_days_history_json_str =json.dumps(price_last_days_history_json, indent=4)

dividends = ticker.dividends
news = ticker.news
dividends_json = get_dividend_object(ticker)
dividends_list = get_dividend_history(ticker)
dividends_json_str = json.dumps(dividends_json)
dividends_history_json = json.dumps(dividends_list, indent=4)

ticker = info.get("symbol", "")
name = info.get("longName", "")
country = info.get('country', 'United States')
exchange = info.get('exchange')
sector = info.get('sector')
website = info.get('website')
price = info.get('currentPrice')
type = info.get("quoteType", None) 

last_row = history.iloc[-1]  

timestamp = int(last_row.name.timestamp())  

previous_close = last_row['Close'] - last_row['Close'] + last_row['Close'] 
change = last_row['Close'] - last_row['Open']  
change_p = (change / last_row['Open']) * 100 if last_row['Open'] != 0 else 0

price_json = {
    "code": symbol,
    "timestamp": timestamp,
    "gmtoffset": 0,  # ajustar se necessário
    "open": float(last_row['Open']),
    "high": float(last_row['High']),
    "low": float(last_row['Low']),
    "close": float(last_row['Close']),
    "volume": int(last_row['Volume']),
    "previousClose": float(last_row['Close']),  # ou pegar history.iloc[-2]['Close'] se houver
    "change": round(change, 4),
    "change_p": round(change_p, 4)
}

currency = info.get("currency", "USD")
year = info.get("year", 2025);
price_json_str = json.dumps(price_json)  
fundamentals_json_str = json.dumps(info)

has_dividends = int(dividends.empty == False)  
news_json = json.dumps(news)

favicon_url = website + "/favicon.ico"

data = {
    "ticker": symbol,
    "name": name,
    "country": country,
    "exchange": exchange,
    "sector": sector,
    "website": website,
    "logo": favicon_url,
    "price_json": price_json_str,
    "fundamentals_json": fundamentals_json_str,
    "dividends_json": dividends_json_str,
    "dividends_history_json": dividends_history_json,
    "has_dividends": has_dividends,
    "price": price,
    "price_start_year": price_start_year,
    "price_last_days_history_json": price_last_days_history_json_str,
    "news_json": news_json,
    "type": type
}

print(json.dumps(data))

