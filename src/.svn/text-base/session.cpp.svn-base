 #include <unistd.h>
#include "session.h"
#include <sys/time.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <stdio.h>
#include <stdlib.h>
#include <iostream>
#include <arpa/inet.h>
#include <netdb.h>
#include <netinet/tcp.h>
#include <errno.h>
#include "log.h"
#include "ttcircle.h"
#include "global.h"
#include <string.h>
#include <vector>

using namespace std;


Session:: Session(int sock)
{
  m_socket = sock;
  m_data.resize(IOBUFSIZE);
  m_rdata.resize(IOBUFSIZE);
  struct timeval opttv;
  opttv.tv_sec = 0;
  opttv.tv_usec = (__suseconds_t)(0.25 * 1000000);
  setsockopt(m_socket, SOL_SOCKET, SO_RCVTIMEO, (char *)&opttv, sizeof(opttv));
  opttv.tv_sec = 0;
  opttv.tv_usec = (__suseconds_t)(0.25 * 1000000);
  setsockopt(m_socket, SOL_SOCKET, SO_SNDTIMEO, (char *)&opttv, sizeof(opttv));
  int optint = 1;
  setsockopt(m_socket, IPPROTO_TCP, TCP_NODELAY, (char *)&optint, sizeof(optint));
}

Session:: ~Session()
{
  close(m_socket);
}

int Session:: Send(int sock,void * sdata,int len)
{
	//LOG_DEBUG(" %s %d %s ",__FILE__,__LINE__,__FUNCTION__);
	int slen = 0;
	char * data = (char *)sdata;
	while( slen < len )
	{
		int l = ::send(sock,data+slen,len - slen,MSG_NOSIGNAL);
		//LOG_DEBUG(" %s %d %s l: %d ",__FILE__,__LINE__,__FUNCTION__,l);
    if( -1 == l )
    {
    	if(errno != EINTR && errno != EAGAIN && errno != EWOULDBLOCK)
    	{
    		return -1;
    	}
    	l = 0 ;
    }
    slen += l;
	}
	
	return slen;
}

void Session:: process()
{	
	if( 0 > m_socket )
	{
		LOG_ERROR(" %s %d %s m_socket error %d .",__FILE__,__LINE__,__FUNCTION__,m_socket);
		return ;
	}
	struct timeval tv_out;
  tv_out.tv_sec = 3;
  tv_out.tv_usec = 0;
	int ret = setsockopt(m_socket, SOL_SOCKET, SO_RCVTIMEO, &tv_out, sizeof(tv_out));
	if( -1 == ret )
	{
		LOG_ERROR(" %s %d %s setsockopt error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
		return ;
	}
  uint16_t cmd = 0;
	while(true)
	{
		int len = 0;
		do
		{
			len = ::recv(m_socket, &m_data[0], IOBUFSIZE, MSG_NOSIGNAL);
			if( len < 0 && EINTR == errno ) continue;
			break;
		}while(true);
	  if( len <= 0 )
	  {
	  	LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
			return ;
	  }
	  LOG_HEX_DEBUG(&m_data[0], len);
	  m_picur = len;
	  m_pib = 0;
	  if( m_picur < (int)(sizeof(cmd)) )
	  {
	  	len = ::recv(m_socket, &m_data[m_picur], IOBUFSIZE-m_picur, MSG_NOSIGNAL);
	  	if( len < (int)(sizeof(cmd)) - m_picur )
	  	{
	  		LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,len);
				return ;
			}
			m_picur += len;
	  }
	  cmd = ntohs(*((uint16_t*)(&m_data[m_pib]) ));
	  m_pib += sizeof(cmd);
	  LOG_INFO(" %s %d %s recv cmd %x .",__FILE__,__LINE__,__FUNCTION__,cmd);
	  if( TTCMD::CMD_MISC == cmd)
	  {
	  	if( 0 != do_misc()) return ;
	  }
		else
		{
	  	LOG_ERROR(" %s %d %s cmd not support %x .",__FILE__,__LINE__,__FUNCTION__,cmd);
	  	return ;
	  }
  }
}

int Session:: do_vanish()
{
	/*
	for(map<string ,TTCircle*>::iterator iter = g_ttc.begin();iter != g_ttc.end();iter++)
	{
		map<string,TTconPool *> pool = iter->second->get_pool();
		for(map<string,TTconPool *>::iterator it=pool.begin();it!=pool.end();it++)
		{
			TTCON* tcon = it->second->GetConnection();
			if( NULL == tcon)
			{
				LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
	  	 	return -1;
			}
			TTCONGuard tg(it->second,tcon);
			int len = Send(tcon->sock,m_data,m_pcur-m_data);
	  	if( len != (m_pcur-m_data) )
	  	{
	  		tcon->status = TTconPool::NOCON;
	  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
	  		return -1;
	  	}
	  	do{
		  	len = ::recv(tcon->sock, m_rdata, IOBUFSIZE, MSG_NOSIGNAL);
		  	if( len < 0 && ( EINTR == errno )) continue;
		  	break;
			}while(true);
		}
	}
	int8_t ret = 0;
	int len = Send(m_socket,&ret,sizeof(ret));
	if( len < 0 )
	{
	  	LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
	  	return -1;
	}
	*/
	return 0;
}

int Session:: do_misc_out(int rnum)
{
		LOG_DEBUG(" %s %d %s misc out. ",__FILE__,__LINE__,__FUNCTION__);
  	if( 0 == rnum )
  	{
  		LOG_ERROR(" %s %d %s rnum = 0 .",__FILE__,__LINE__,__FUNCTION__);
			return -1;
  	}
  	int len = 0;
  	if( m_picur - m_pib < (int)sizeof(int) )
		{
			len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
			if( len < (int)sizeof(int)-(m_picur-m_pib) )
			{
		  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
				return -1;
			}
			m_picur += len;
		}
		int key_len = ntohl( *((int*)(&m_data[m_pib]) ) );
		m_pib += sizeof(int);
	  LOG_DEBUG(" %s %d %s key_len : %d ",__FILE__,__LINE__,__FUNCTION__,key_len);

  	if( m_picur - m_pib < key_len )
		{
			len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
			if( len < key_len-(m_picur-m_pib) )
			{
		  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
				return -1;
			}
			m_picur += len;
		}
		char * key = &m_data[m_pib];
		m_pib += key_len;
		string skey = string(key,key_len);
	  LOG_DEBUG(" %s %d %s key : %s ",__FILE__,__LINE__,__FUNCTION__,skey.c_str() );

	  int lastindex = skey.find_last_of('/');
	  if( (int)string::npos == lastindex )
	  {
	  	LOG_ERROR(" %s %d %s key error not find / .",__FILE__,__LINE__,__FUNCTION__);
	  	return -1;
	  }
	  string table = skey.substr(0,lastindex);
	  stringReplace(table,"/","_");
	  map<string ,TTCircle*>::iterator iter = g_ttc.find(table);
	  if( g_ttc.end() == iter )
	  {
	  	LOG_ERROR(" %s %d %s table not find %s .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
	  	return -1;
	  }
	  string realkey = skey;
	  int lindex = skey.find_last_of('|');
	  if( (int)string::npos != lindex )
	  {
	  	realkey = skey.substr(0,lindex);
	  }
  	TTconPool * tp = iter->second->GetTTPool(realkey);
		TTCON* tcon = tp->GetConnection();
		if( NULL == tcon)
		{
			LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
  	 	return -1;
		}
		TTCONGuard tg(tp,tcon);
		len = Send(tcon->sock,&m_data[0],m_picur);
		LOG_HEX_DEBUG(&m_data[0], m_picur);
  	if( len != m_picur )
  	{
  		tcon->status = TTconPool::NOCON;
  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
  		return -1;
  	}
  	do{
	  	len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
	  	if( len < 0 && ( EINTR == errno )) continue;
	  	break;
		}while(true);
	  if( len <= 0 )
	  {
	  	tcon->status = TTconPool::NOCON;
	  	LOG_ERROR(" %s %d %s recv len %d error %d %s.",__FILE__,__LINE__,__FUNCTION__,len,errno ,strerror(errno));
			return -1;
	  }
	  LOG_HEX_DEBUG(&m_rdata[0], len);
	  m_pirb = 0;
	  m_pircur = len;
  	len = Send(m_socket,&m_rdata[0], m_pircur );
  	LOG_HEX_DEBUG(&m_rdata[0], m_pircur);
  	if( len != m_pircur )
  	{
  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
  		return -1;
  	}

	return 0;
	
}
int Session:: do_misc_put(int rnum)
{
		LOG_DEBUG(" %s %d %s misc put. ",__FILE__,__LINE__,__FUNCTION__);
  	if( 0 == rnum )
  	{
  		LOG_ERROR(" %s %d %s rnum = 0 .",__FILE__,__LINE__,__FUNCTION__);
			return -1;
  	}
  	int len = 0;
  	if( m_picur - m_pib < (int)sizeof(int) )
		{
			len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
			if( len < sizeof(int)-(m_picur-m_pib) )
			{
		  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
				return -1;
			}
			m_picur += len;
		}
		int key_len = ntohl( *((int*)(&m_data[m_pib]) ) );
		m_pib += sizeof(int);
	  LOG_DEBUG(" %s %d %s key_len : %d ",__FILE__,__LINE__,__FUNCTION__,key_len);

  	if( m_picur - m_pib < key_len )
		{
			len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
			if( len < key_len-(m_picur-m_pib) )
			{
		  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
				return -1;
			}
			m_picur += len;
		}
		char * key = &m_data[m_pib];
		m_pib += key_len;
		string skey = string(key,key_len);
	  LOG_DEBUG(" %s %d %s key : %s ",__FILE__,__LINE__,__FUNCTION__,skey.c_str() );

	  for(int i=1;i<rnum;i++)
	  {
	  	if( m_picur - m_pib < sizeof(int) )
			{
				if( m_data.size() - m_picur < sizeof(int) )
				{
						m_data.resize( 2 * m_data.size() );
				}
				len = ::recv(m_socket, &m_data[m_picur], m_data.size() - m_picur , MSG_NOSIGNAL);
				if( len < (int)sizeof(int)-(m_picur-m_pib) )
				{
			  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
					return -1;
				}
				m_picur += len;
			}
			int value_len = ntohl( *((int*)(&m_data[m_pib]) ) );
			m_pib += sizeof(int);
		  LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
		  if( 0 >= value_len ) continue;
		  if( m_picur - m_pib < value_len )
			{
				if( m_data.size() - m_picur < value_len )
				{
						m_data.resize( 2 * m_data.size() );
				}
				len = ::recv(m_socket, &m_data[m_picur], m_data.size() - m_picur, MSG_NOSIGNAL);
				if( len < value_len-(m_picur-m_pib) )
				{
			  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
					return -1;
				}
				m_picur += len;
			}
			char * value = &m_data[m_pib];
			m_pib += value_len;
		  LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );
		  
	  }
	  int lastindex = skey.find_last_of('/');
	  if( (int)string::npos == lastindex )
	  {
	  	LOG_ERROR(" %s %d %s key error not find / .",__FILE__,__LINE__,__FUNCTION__);
	  	return -1;
	  }
	  string table = skey.substr(0,lastindex);
	  stringReplace(table,"/","_");
	  LOG_ERROR(" %s %d %s table %s .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
	  map<string ,TTCircle*>::iterator iter = g_ttc.find(table);
	  if( g_ttc.end() == iter )
	  {
	  	LOG_ERROR(" %s %d %s table not find %s .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
	  	return -1;
	  }
	  string realkey = skey;
	  int lindex = skey.find_last_of('|');
	  if( (int)string::npos != lindex )
	  {
	  	LOG_ERROR(" %s %d %s skey find | %d .",__FILE__,__LINE__,__FUNCTION__,lindex);
	  	realkey = skey.substr(0,lindex);
	  }
  	TTconPool * tp = iter->second->GetTTPool(realkey);
		TTCON* tcon = tp->GetConnection();
		if( NULL == tcon)
		{
			LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
  	 	return -1;
		}
		TTCONGuard tg(tp,tcon);
		len = Send(tcon->sock,&m_data[0],m_picur);
		LOG_HEX_DEBUG(&m_data[0], m_picur);
  	if( len != m_picur )
  	{
  		tcon->status = TTconPool::NOCON;
  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
  		return -1;
  	}
  	do{
	  	len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
	  	if( len < 0 && ( EINTR == errno )) continue;
	  	break;
		}while(true);
	  if( len <= 0 )
	  {
	  	tcon->status = TTconPool::NOCON;
	  	LOG_ERROR(" %s %d %s recv len %d error %d %s.",__FILE__,__LINE__,__FUNCTION__,len,errno ,strerror(errno));
			return -1;
	  }
	  LOG_HEX_DEBUG(&m_rdata[0], len);
	  m_pirb = 0;
	  m_pircur = len;
  	len = Send(m_socket,&m_rdata[0], m_pircur );
  	LOG_HEX_DEBUG(&m_rdata[0], m_pircur);
  	if( len != m_pircur )
  	{
  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
  		return -1;
  	}

	return 0;
}

int Session:: do_misc_get(int rnum)
{
		LOG_DEBUG(" %s %d %s misc get. ",__FILE__,__LINE__,__FUNCTION__);
  	if( 0 == rnum )
  	{
  		LOG_ERROR(" %s %d %s rnum = 0 .",__FILE__,__LINE__,__FUNCTION__);
			return -1;
  	}
  	int len = 0;
  	if( m_picur - m_pib < (int)sizeof(int) )
		{
			len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
			if( len < (int)sizeof(int)-(m_picur-m_pib) )
			{
		  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
				return -1;
			}
			m_picur += len;
		}
		int key_len = ntohl( *((int*)(&m_data[m_pib]) ) );
		m_pib += sizeof(int);
	  LOG_DEBUG(" %s %d %s key_len : %d ",__FILE__,__LINE__,__FUNCTION__,key_len);

  	if( m_picur - m_pib < key_len )
		{
			len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
			if( len < key_len-(m_picur-m_pib) )
			{
		  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
				return -1;
			}
			m_picur += len;
		}
		char * key = &m_data[m_pib];
		m_pib += key_len;
		string skey = string(key,key_len);
	  LOG_DEBUG(" %s %d %s key : %s ",__FILE__,__LINE__,__FUNCTION__,skey.c_str() );

	  int lastindex = skey.find_last_of('/');
	  if( (int)string::npos == lastindex )
	  {
	  	LOG_ERROR(" %s %d %s key error not find / .",__FILE__,__LINE__,__FUNCTION__);
	  	return -1;
	  }
	  string table = skey.substr(0,lastindex);
	  stringReplace(table,"/","_");
	  map<string ,TTCircle*>::iterator iter = g_ttc.find(table);
	  if( g_ttc.end() == iter )
	  {
	  	LOG_ERROR(" %s %d %s table not find %s .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
	  	return -1;
	  }
	  string realkey = skey;
	  int lindex = skey.find_last_of('|');
	  if( (int)string::npos != lindex )
	  {
	  	realkey = skey.substr(0,lindex);
	  }
  	TTconPool * tp = iter->second->GetTTPool(realkey);
		TTCON* tcon = tp->GetConnection();
		if( NULL == tcon)
		{
			LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
  	 	return -1;
		}
		TTCONGuard tg(tp,tcon);
		len = Send(tcon->sock,&m_data[0],m_picur);
		LOG_HEX_DEBUG(&m_data[0], m_picur);
  	if( len != m_picur )
  	{
  		tcon->status = TTconPool::NOCON;
  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
  		return -1;
  	}
  	do{
	  	len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
	  	if( len < 0 && ( EINTR == errno )) continue;
	  	break;
		}while(true);
		if( len < 0 )
		{
			tcon->status = TTconPool::NOCON;
	  	LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
			return -1;
		}
		m_pirb = 0;
	  m_pircur = len;
	  if( len < (sizeof(int8_t)+sizeof(int32_t)) )
	  {
	  	len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
			if( len < (sizeof(int8_t)+sizeof(int32_t)) -(m_pircur-m_pirb) )
			{
				tcon->status = TTconPool::NOCON;
	  		LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
				return -1;
			}
			m_pircur += len;
	  }
	  LOG_HEX_DEBUG(&m_rdata[0], m_pircur);
	  int8_t ret = *((int8_t *)(&m_rdata[(m_pirb++)]) );
	  int32_t retnum = ntohl(*((int32_t *)(&m_rdata[m_pirb])));
		m_pirb += sizeof(int32_t);
	  for(int i=0;i<retnum;i++)
	  {
			if( m_pircur - m_pirb < (int)sizeof(int) )
			{
				if( m_rdata.size()-m_pirb < (int)sizeof(int) )
				{
					m_rdata.resize( 2* m_rdata.size() );
				}
				len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
				if( len < (int)sizeof(int)-(m_pircur-m_pirb) )
				{
					tcon->status = TTconPool::NOCON;
				  LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
					return -1;
				}
				m_pircur += len;
			}
			int value_len = ntohl( *((int*)(&m_rdata[m_pirb]) ) );
			m_pirb += (int)sizeof(int);
		  LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
		  if( m_pircur - m_pirb < value_len )
			{
					if( m_rdata.size()-m_pirb < value_len)
					{
							m_rdata.resize( 2* m_rdata.size() + value_len );
					}
					do{
						len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
							LOG_DEBUG(" %s %d %s recv date len  %d  [unread len]%d .",__FILE__,__LINE__,__FUNCTION__,len,(m_pircur-m_pirb) );
						if( len <= 0 ) break;
						else
						{
							
							m_pircur += len;
							if( value_len <= (int)(m_pircur-m_pirb) )
							{
								break;
							}
						}
					}while(true);
					if( len <= 0 )
					{
						tcon->status = TTconPool::NOCON;
			  		LOG_ERROR(" %s %d %s recv date error  %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno) );
						return -1;
					}
			}
			char * value = &m_rdata[m_pirb];
			m_pirb += value_len;
		  LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );
	  }

	  if( iter->second->Consist() ||( !iter->second->Consist() && 0 == ret && 0 != retnum ) )
	  {
		  len = Send(m_socket,&m_rdata[0], m_pircur );
		  LOG_HEX_DEBUG(&m_rdata[0], m_pircur);
	  	if( len != m_pircur )
	  	{
	  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
	  		return -1;
	  	}
	  }
	  else
	  {
	  	TTconPool * ttp = iter->second->GetTTPoolOld(realkey);
			TTCON* ttcon = ttp->GetConnection();
			if( NULL == ttcon)
			{
					LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
		  	 	return -1;
			}
			tg.exchange(ttp,ttcon);
			len = Send(ttcon->sock,&m_data[0],m_picur);
			LOG_HEX_DEBUG(&m_data[0], m_picur);
	  	if( len != m_picur )
	  	{
	  		ttcon->status = TTconPool::NOCON;
	  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
	  		return -1;
	  	}
	  	do{
		  	len = ::recv(ttcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
		  	if( len < 0 && ( EINTR == errno )) continue;
		  	break;
			}while(true);
			if( len < 0 )
			{
				ttcon->status = TTconPool::NOCON;
		  	LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
				return -1;
			}
		  m_pirb = 0;
	  	m_pircur = len;
		  if( len < (sizeof(int8_t)+sizeof(int32_t)) )
		  {
		  	len = ::recv(ttcon->sock, &m_rdata[m_pircur], m_rdata.size()- m_pircur, MSG_NOSIGNAL);
				if( len < (int)(sizeof(int8_t)+sizeof(int32_t)) -(m_pircur-m_pirb) )
				{
					ttcon->status = TTconPool::NOCON;
		  		LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
					return -1;
				}
				m_pircur += len;
		  }
		  LOG_HEX_DEBUG(&m_rdata[0], m_pircur);
			ret = *((int8_t *)(&m_data[m_pirb++]));
	  	retnum = ntohl(*((int32_t *)(&m_data[m_pirb])));
	  	m_pirb += sizeof(int32_t);
	  	for(int i=0;i<retnum;i++)
	  	{
		  	if( m_pircur - m_pirb < (int)sizeof(int) )
				{
					if( m_rdata.size()-m_pirb < (int)sizeof(int) )
					{
							m_rdata.resize( 2* m_rdata.size()  );
					}
					len = ::recv(ttcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
					if( len < (int)sizeof(int)-(m_pircur-m_pirb) )
					{
						ttcon->status = TTconPool::NOCON;
				  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
						return -1;
					}
					m_pircur += len;
				}
				int value_len = ntohl( *((int*)(&m_rdata[m_pirb]) ) );
				m_pirb += sizeof(int);
		  	LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
		  	if( m_pircur - m_pirb < value_len )
				{
					if( m_rdata.size()-m_pirb < value_len)
					{
							m_rdata.resize( 2* m_rdata.size() + value_len );
					}
					do{
						len = ::recv(ttcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
							LOG_DEBUG(" %s %d %s recv date len  %d  [unread len]%d .",__FILE__,__LINE__,__FUNCTION__,len,(m_pircur-m_pirb) );
						if( len <= 0 ) break;
						else
						{
							
							m_pircur += len;
							if( value_len <= (int)(m_pircur-m_pirb) )
							{
								break;
							}
						}
					}while(true);
					if( len <= 0 )
					{
						ttcon->status = TTconPool::NOCON;
			  		LOG_ERROR(" %s %d %s recv date error  %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno) );
						return -1;
					}
				}
				char * value = &m_rdata[m_pirb];
				m_pirb += value_len;
		  	LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );

	  	}
		  len = Send(m_socket,&m_rdata[0], m_pircur );
		  LOG_HEX_DEBUG(&m_rdata[0], m_pircur);
	  	if( len != m_pircur )
	  	{
	  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
	  		return -1;
	  	}
	  }  	
	
	return 0;	
}

int Session:: do_misc_setindex(int rnum)
{
			LOG_DEBUG(" %s %d %s misc setindex. ",__FILE__,__LINE__,__FUNCTION__);
  	if( 0 == rnum )
  	{
  		LOG_ERROR(" %s %d %s rnum = 0 .",__FILE__,__LINE__,__FUNCTION__);
			return -1;
  	}
  	int len = 0;
  	if( m_picur - m_pib < sizeof(int) )
		{
			len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
			if( len < sizeof(int)-(m_picur-m_pib) )
			{
		  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
				return -1;
			}
			m_picur += len;
		}
		int key_len = ntohl( *((int*)(&m_data[m_pib]) ) );
		m_pib += sizeof(int);
	  LOG_DEBUG(" %s %d %s key_len : %d ",__FILE__,__LINE__,__FUNCTION__,key_len);

  	if( m_picur - m_pib < key_len )
		{
			len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
			if( len < key_len-(m_picur-m_pib) )
			{
		  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
				return -1;
			}
			m_picur += len;
		}
		char * key = &m_data[m_pib];
		m_pib += key_len;
		string skey = string(key,key_len);
	  LOG_DEBUG(" %s %d %s key : %s ",__FILE__,__LINE__,__FUNCTION__,skey.c_str() );

	  int lastindex = skey.find_last_of('/');
	  if( (int)string::npos == lastindex )
	  {
	  	LOG_ERROR(" %s %d %s key error not find / .",__FILE__,__LINE__,__FUNCTION__);
	  	return -1;
	  }
	  string table = skey.substr(0,lastindex);
	  stringReplace(table,"/","_");
	  map<string ,TTCircle*>::iterator iter = g_ttc.find(table);
	  if( g_ttc.end() == iter )
	  {
	  	LOG_ERROR(" %s %d %s table not find %s .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
	  	return -1;
	  }
	  LOG_ERROR(" %s %d %s table  %s .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
		*( (int*)(key-4) ) = htonl(key_len - lastindex -1);
    memmove(key,key+lastindex+1,m_picur-m_pib+(key_len-lastindex-1));
		m_picur -= (lastindex +1 ); 
		map<string,TTconPool *> pool = iter->second->get_pool();
		for(map<string,TTconPool *>::iterator it=pool.begin();it!=pool.end();it++)
		{
			TTCON* tcon = it->second->GetConnection();
			if( NULL == tcon)
			{
				LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
	  	 	return -1;
			}
			TTCONGuard tg(it->second,tcon);
			int len = Send(tcon->sock,&m_data[0],m_picur );
			LOG_HEX_DEBUG(&m_data[0], m_picur);
	  	if( len != m_picur )
	  	{
	  		tcon->status = TTconPool::NOCON;
	  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
	  		return -1;
	  	}
	  	do{
		  	len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
		  	if( len < 0 && ( EINTR == errno )) continue;
		  	break;
			}while(true);
			if( len <  0 )
			{
				tcon->status = TTconPool::NOCON;
				LOG_ERROR(" %s %d %s recv error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
	  		return -1;
			}
		}
		char ret[5] = {0};
		len = Send(m_socket,ret,sizeof(ret));
		if( len < 0 )
	  {
	  	LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
	  	return -1;
	  }

	return 0;
}

int Session:: do_misc_search(int rnum,int prnum)
{
		LOG_DEBUG(" %s %d %s misc search. ",__FILE__,__LINE__,__FUNCTION__);
  	if( 2 > rnum )
  	{
  		LOG_ERROR(" %s %d %s rnum < 2 .",__FILE__,__LINE__,__FUNCTION__);
			return -1;
  	}
		int value_len = 0;
		char * value = NULL;
		bool blimit =false;
		int max = 0;
		int skip = 0;
		int mpmax = 0;
		string table;
		int len = 0;
	  for(int i=0;i<rnum;i++)
	  {
	  	if( m_picur - m_pib < sizeof(int) )
			{
				len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
				if( len < (int)sizeof(int)-(m_picur-m_pib) )
				{
			  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
					return -1;
				}
				m_picur += len;
			}
			value_len = ntohl( *((int*)(&m_data[m_pib])) );
			m_pib += sizeof(int);
		  LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
		  if( m_picur - m_pib < value_len )
			{
				len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
				if( len < value_len-(m_picur-m_pib) )
				{
			  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
					return -1;
				}
				m_picur += len;
			}
			value = &m_data[m_pib];
			if( 0 == strncmp(value,"addcond",7)  && 0 == strncmp((value+8),"tablename",9) )
			{
				for(int t=0;t<12;t++)
				{
					if( '\0' == *(value+18+t))
					{
						table = string( (value+19+t),value_len-(19+t) );
						LOG_DEBUG(" %s %d %s  table : %s ",__FILE__,__LINE__,__FUNCTION__,table.c_str() );				
						break;
					}
				}
			}
			else if( 0 == strncmp(value,"setlimit",8) )
			{
				blimit = true;
				mpmax = m_pib+9;
				max = atoi(&m_data[mpmax]);
				char pskip[12]={0};
				strncpy(pskip,&m_data[(m_pib + 9 + strlen(&m_data[mpmax]) + 1 ) ],value_len-(9 + strlen(&m_data[mpmax]) + 1));
				skip = atoi( pskip );
				LOG_DEBUG(" %s %d %s max %d skip %d .",__FILE__,__LINE__,__FUNCTION__,max,skip );
			}
			m_pib += value_len;
		  LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );
	  }
		stringReplace(table,"/","_");
		LOG_DEBUG(" %s %d %s table : [%s] ",__FILE__,__LINE__,__FUNCTION__,table.c_str() );
	  map<string ,TTCircle*>::iterator iter = g_ttc.find(table);
	  if( g_ttc.end() == iter )
	  {
	  	LOG_ERROR(" %s %d %s table not find [%s] .",__FILE__,__LINE__,__FUNCTION__,table.c_str());
	  	return -1;
	  }
		if( 0 == strncmp(value,"count",5) )
		{
			return do_misc_search_count(iter);
		}
		else if( 0 == strncmp(value,"out",3) )
		{
			return do_misc_search_out(iter);
		}
		else if(blimit)
		{
			return do_misc_search_limit(iter,mpmax,skip,max);
		}
		else
		{
			return do_misc_search_all(iter);			
		}
	return 0;
}

int Session:: do_misc_search_all(map<string ,TTCircle*>::iterator iter)
{
			map<string,TTconPool *> pool = iter->second->get_pool();
			int32_t tretnum = 0;
			vector<char> v_data;
			v_data.resize(IOBUFSIZE);
			int32_t vlen = 0;
			v_data[0]=0;
			vlen = 5;
			for(map<string,TTconPool *>::iterator it=pool.begin();it!=pool.end();it++)
			{
				TTCON* tcon = it->second->GetConnection();
				if( NULL == tcon)
				{
					LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
		  	 	return -1;
				}
				TTCONGuard tg(it->second,tcon);
				int len = Send(tcon->sock,&m_data[0],m_picur);
				LOG_HEX_DEBUG(&m_data[0], m_picur);
		  	if( len != m_picur )
		  	{
		  		tcon->status = TTconPool::NOCON;
		  		LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
		  		return -1;
		  	}
		  	
		  	do{
			  	len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
			  	if( len < 0 && ( EINTR == errno )) continue;
			  	break;
				}while(true);
				LOG_ERROR(" %s %d %s recv len %d .",__FILE__,__LINE__,__FUNCTION__,len);
			  if( len < (int)(sizeof(int8_t)+sizeof(int32_t)) )
			  {
			  	tcon->status = TTconPool::NOCON;
			  	LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
					return -1;
			  }
			  LOG_HEX_DEBUG(&m_rdata[0], len);
			  m_pirb = 0;
		  	m_pircur = len;
				int8_t ret = *((int8_t *)(&m_rdata[m_pirb++]));
		  	int32_t retnum = ntohl(*((int32_t *)(&m_rdata[m_pirb])));
		  	tretnum += retnum ;
		  	m_pirb += sizeof(int32_t);
		  	int value_len = 0;
		  	char * value = NULL;
		  	for(int i=0;i<retnum;i++)
		  	{
			  	if( m_pircur - m_pirb < (int)sizeof(int) )
					{
						if( m_rdata.size() - m_pircur < (int)sizeof(int) )
						{
							m_rdata.resize( 2* m_rdata.size());
						}
						len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
						if( len < (int)sizeof(int)-(m_pircur-m_pirb) )
						{
							tcon->status = TTconPool::NOCON;
					  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
							return -1;
						}
						m_pircur += len;
					}
					value_len = ntohl( *((int*)(&m_rdata[m_pirb])) );
					m_pirb += sizeof(int);
			  	LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
			  	if( m_pircur - m_pirb < value_len )
					{
						if( m_rdata.size() - m_pircur < value_len )
						{
							m_rdata.resize( 2* m_rdata.size() + value_len );
						}
						len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size() - m_pircur, MSG_NOSIGNAL);
						if( len < value_len-(m_pircur-m_pirb) )
						{
							tcon->status = TTconPool::NOCON;
				  		LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
							return -1;
						}
						m_pircur += len;
					}
					value = &m_rdata[m_pirb];
					m_pirb += value_len;
			  	LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );
		  		
		  	}
		  	
				int32_t dlen = m_pircur - 5;
				if( retnum > 0 ) 
				{
						tretnum --;
						dlen = dlen - value_len - 4;
				}
				if( dlen > (v_data.size() - vlen + 5) )
				{
					v_data.resize( 2 * v_data.size() + 2*dlen);
				}
				memcpy(&v_data[vlen],&m_rdata[5],dlen);
	
				vlen += dlen;
			}
			LOG_DEBUG(" %s %d %s tretnum : %d ",__FILE__,__LINE__,__FUNCTION__,tretnum );
			*(int32_t*)(&v_data[1]) = htonl(tretnum);
			int len = Send(m_socket,&v_data[0], vlen );
			LOG_HEX_DEBUG(&v_data[0], vlen);
		  if( len != vlen )
		  {
		  		LOG_ERROR(" %s %d %s send error len %d (m_prcur - m_rdata) %d .",__FILE__,__LINE__,__FUNCTION__,len,m_pircur);
		  		return -1;
		  }

	return 0;
}

int Session:: do_misc_search_limit(map<string ,TTCircle*>::iterator iter,int mpmax,int skip,int max)
{
			m_picur = mpmax - 13 ;
			map<string,TTconPool *> pool = iter->second->get_pool();
			int32_t tretnum = 0;
			vector<char> v_data;
			v_data.resize(IOBUFSIZE);
			int32_t vlen = 0;
			v_data[0]=0;
			vlen = 5;
			int32_t tcount = 0;
			for(map<string,TTconPool *>::iterator it=pool.begin();it!=pool.end();it++)
			{
				TTCON* tcon = it->second->GetConnection();
				if( NULL == tcon)
				{
					LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
		  	 	return -1;
				}
				TTCONGuard tg(it->second,tcon);
				int count = 0 ;
				if( it != pool.begin() )
				{	
					if( tretnum >= max || tcount >= skip + max )
					{
						break;
					}
				}
				*( (int32_t*)(&m_data[m_picur])) = htonl(5);
				sprintf(&m_data[m_picur+4],"%s","count");
				int len = Send(tcon->sock,&m_data[0],m_picur + 9);
				LOG_HEX_DEBUG(&m_data[0], m_picur + 9);
				if( len != (m_picur+9) )
			  {
			  	tcon->status = TTconPool::NOCON;
			  	LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
			  	return -1;
			  }
			  do{
					len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
					if( len < 0 && ( EINTR == errno )) continue;
					break;
				}while(true);
				LOG_ERROR(" %s %d %s recv len %d .",__FILE__,__LINE__,__FUNCTION__,len);
				if( len < (int)(sizeof(int8_t)+sizeof(int32_t)) )
				{
					tcon->status = TTconPool::NOCON;
					LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
					return -1;
				}
				LOG_HEX_DEBUG(&m_rdata[0], len);
				m_pirb = 0;
		  	m_pircur = len;
				int8_t ret = *((int8_t *)(&m_rdata[m_pirb++]));
		  	int32_t retnum = ntohl(*((int32_t *)(&m_rdata[m_pirb])));
		  	m_pirb += sizeof(int32_t);
		  	int value_len = 0;
		  	char * value = NULL;
		  	for(int i=0;i<retnum;i++)
		  	{
			  	if( m_pircur - m_pirb < (int)sizeof(int) )
					{
						len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
						if( len < sizeof(int)-(m_pircur-m_pirb) )
						{
							tcon->status = TTconPool::NOCON;
				  		LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
							return -1;
						}
						m_pircur += len;
					}
					value_len = ntohl( *((int*)(&m_rdata[m_pirb])) );
					m_pirb += sizeof(int);
			  	LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
			  	if( m_pircur - m_pirb < value_len )
					{
						len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
						if( len < value_len-(m_pircur-m_pirb) )
						{
							tcon->status = TTconPool::NOCON;
							LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
							return -1;
						}
						m_pircur += len;
					}
					value = &m_rdata[m_pirb];
					if( 0 == i )
					{ 
						count = atoi(value);
						LOG_DEBUG(" %s %d %s count : %d ",__FILE__,__LINE__,__FUNCTION__,count );
					}
					m_pirb += value_len;
			  	LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );
		  	
		  	}
		  	if( 0 == count) continue;
		  	else 
		  	{
		  		if( tcount + count < skip )
		  		{
		  			tcount += count;
		  			continue;
		  		}
		  	}
		  	int lskip = (skip - tcount) > 0 ? (skip - tcount) : 0;	  		
		  	int lmax = (skip - tcount) < 0 ? (max + (skip - tcount)) : max ;
				tcount += count;
				int n1 = sprintf((&m_data[m_picur+4]),"%s","setlimit");
				m_data[m_picur+4+n1] = '\0';
				int n = 4 + n1 +1 ;
				int n2 = sprintf(&m_data[m_picur+n],"%d",lmax);
				m_data[m_picur+n+n2] = '\0';
				n += (n2 + 1);
				int n3 = sprintf(&m_data[m_picur+n],"%d",lskip);
				n += n3;
				*( (int32_t*)(&m_data[m_picur])) = htonl(n-4);
				len = Send(tcon->sock,&m_data[0],m_picur+n);
				LOG_HEX_DEBUG(&m_data[0], m_picur + n);
			  if( len != (m_picur+n) )
			  {
			  	tcon->status = TTconPool::NOCON;
			  	LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
			  	return -1;
			  }
		  	
		  	do{
			  	len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
			  	if( len < 0 && ( EINTR == errno )) continue;
			  	break;
				}while(true);
				LOG_ERROR(" %s %d %s recv len %d .",__FILE__,__LINE__,__FUNCTION__,len);
			  if( len < (int)(sizeof(int8_t)+sizeof(int32_t)) )
			  {
			  	tcon->status = TTconPool::NOCON;
			  	LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
					return -1;
			  }
			  LOG_HEX_DEBUG(&m_rdata[0], len);
			  m_pirb = 0;
		  	m_pircur = len;
				ret = *((int8_t *)(&m_rdata[m_pirb++]));
		  	retnum = ntohl(*((int32_t *)(&m_rdata[m_pirb])));
		  	tretnum += retnum ;
		  	m_pirb += sizeof(int32_t);
		  	for(int i=0;i<retnum;i++)
		  	{
			  	if( m_pircur - m_pirb < (int)sizeof(int) )
					{
						if( m_rdata.size() - m_pircur < (int)sizeof(int) )
						{
							m_rdata.resize( 2* m_rdata.size());
						}
						len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
						if( len < (int)sizeof(int)-(m_pircur-m_pirb) )
						{
							tcon->status = TTconPool::NOCON;
					  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
							return -1;
						}
						m_pircur += len;
					}
					value_len = ntohl( *((int*)(&m_rdata[m_pirb])) );
					m_pirb += sizeof(int);
			  	LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
			  	if( m_pircur - m_pirb < value_len )
					{
						if( m_rdata.size() - m_pircur < value_len )
						{
							m_rdata.resize( 2* m_rdata.size() + value_len );
						}
						len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size() - m_pircur, MSG_NOSIGNAL);
						if( len < value_len-(m_pircur-m_pirb) )
						{
							tcon->status = TTconPool::NOCON;
				  		LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
							return -1;
						}
						m_pircur += len;
					}
					value = &m_rdata[m_pirb];
					m_pirb += value_len;
			  	LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );
		  		
		  	}
		  	
				int32_t dlen = m_pircur - 5;
				if( retnum > 0 ) 
				{
						tretnum --;
						dlen = dlen - value_len - 4;
				}
				if( dlen > (v_data.size() - vlen + 5) )
				{
					v_data.resize( 2 * v_data.size() + 2*dlen);
				}
				memcpy(&v_data[vlen],&m_rdata[5],dlen);
	
				vlen += dlen;
			}
			LOG_DEBUG(" %s %d %s tretnum : %d ",__FILE__,__LINE__,__FUNCTION__,tretnum );
			*(int32_t*)(&v_data[1]) = htonl(tretnum);
			int len = Send(m_socket,&v_data[0], vlen );
			LOG_HEX_DEBUG(&v_data[0], vlen);
		  if( len != vlen )
		  {
		  		LOG_ERROR(" %s %d %s send error len %d (m_prcur - m_rdata) %d .",__FILE__,__LINE__,__FUNCTION__,len,m_pircur);
		  		return -1;
		  }

	return 0;
}

int Session:: do_misc_search_out(map<string ,TTCircle*>::iterator iter)
{
		map<string,TTconPool *> pool = iter->second->get_pool();
		for(map<string,TTconPool *>::iterator it=pool.begin();it!=pool.end();it++)
		{
			TTCON* tcon = it->second->GetConnection();
			if( NULL == tcon)
			{
				LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
		 		return -1;
			}
			TTCONGuard tg(it->second,tcon);
			int len = Send(tcon->sock,&m_data[0],m_picur + 9);
			LOG_HEX_DEBUG(&m_data[0], m_picur + 9);
			if( len != (m_picur+9) )
			{
				tcon->status = TTconPool::NOCON;
				LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
				return -1;
			}
			do{
		  	len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
		  	if( len < 0 && ( EINTR == errno )) continue;
		  	break;
			}while(true);
			LOG_ERROR(" %s %d %s recv len %d .",__FILE__,__LINE__,__FUNCTION__,len);
		  if( len < (int)(sizeof(int8_t)+sizeof(int32_t)) )
		  {
		  	tcon->status = TTconPool::NOCON;
		  	LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
				return -1;
		  }
		  LOG_HEX_DEBUG(&m_rdata[0], len);
			m_pirb = 0;
			m_pircur = len;
			int8_t ret = *((int8_t *)(&m_rdata[m_pirb++]));
			int32_t retnum = ntohl(*((int32_t *)(&m_rdata[m_pirb])));
			m_pirb += sizeof(int32_t);
			int value_len = 0;
			char * value = NULL;
			for(int i=0;i<retnum;i++)
			{
				if( m_pircur - m_pirb < (int)sizeof(int) )
				{
					len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
					if( len < sizeof(int)-(m_pircur-m_pirb) )
					{
						tcon->status = TTconPool::NOCON;
			  		LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
						return -1;
					}
					m_pircur += len;
				}
				value_len = ntohl( *((int*)(&m_rdata[m_pirb])) );
				m_pirb += sizeof(int);
				LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
				if( m_pircur - m_pirb < value_len )
				{
					len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
					if( len < value_len-(m_pircur-m_pirb) )
					{
						tcon->status = TTconPool::NOCON;
		  			LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
						return -1;
					}
					m_pircur += len;
				}
				value = &m_rdata[m_pirb];
				m_pirb += value_len;
				LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );
			}
		}
		m_pirb = m_pircur = 0;
		m_rdata[m_pircur++] = 0;
		*((int *)(&m_rdata[m_pircur])) = htonl(0);
		m_pircur += (int)sizeof(int);
		int len = Send(m_socket,&m_rdata[0],m_pircur);
		LOG_HEX_DEBUG(&m_rdata[0], m_pircur);
		if( len < 0 )
		{
			LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
			return -1;
		}
	return 0;
}

int Session:: do_misc_search_count(map<string ,TTCircle*>::iterator iter)
{
		int count = 0;
		int tcount = 0;
		map<string,TTconPool *> pool = iter->second->get_pool();
		for(map<string,TTconPool *>::iterator it=pool.begin();it!=pool.end();it++)
		{
			TTCON* tcon = it->second->GetConnection();
			if( NULL == tcon)
			{
				LOG_ERROR(" %s %d %s can not get ttconnect .",__FILE__,__LINE__,__FUNCTION__);
		 		return -1;
			}
			TTCONGuard tg(it->second,tcon);
			int len = Send(tcon->sock,&m_data[0],m_picur );
			LOG_HEX_DEBUG(&m_data[0], m_picur );
			if( len != (m_picur) )
			{
				tcon->status = TTconPool::NOCON;
				LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
				return -1;
			}
			
			do{
		  	len = ::recv(tcon->sock, &m_rdata[0], m_rdata.size(), MSG_NOSIGNAL);
		  	if( len < 0 && ( EINTR == errno )) continue;
		  	break;
			}while(true);
			LOG_ERROR(" %s %d %s recv len %d .",__FILE__,__LINE__,__FUNCTION__,len);
		  if( len < (int)(sizeof(int8_t)+sizeof(int32_t)) )
		  {
		  	tcon->status = TTconPool::NOCON;
		  	LOG_ERROR(" %s %d %s recv error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
				return -1;
		  }
		  LOG_HEX_DEBUG(&m_rdata[0], len);
			m_pirb = 0;
			m_pircur = len;
			int8_t ret = *((int8_t *)(&m_rdata[m_pirb++]));
			int32_t retnum = ntohl(*((int32_t *)(&m_rdata[m_pirb])));
			m_pirb += sizeof(int32_t);
			int value_len = 0;
			char * value = NULL;
			for(int i=0;i<retnum;i++)
			{
				if( m_pircur - m_pirb < (int)sizeof(int) )
				{
					len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
					if( len < sizeof(int)-(m_pircur-m_pirb) )
					{
						tcon->status = TTconPool::NOCON;
			  		LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
						return -1;
					}
					m_pircur += len;
				}
				value_len = ntohl( *((int*)(&m_rdata[m_pirb])) );
				m_pirb += sizeof(int);
				LOG_DEBUG(" %s %d %s value_len : %d ",__FILE__,__LINE__,__FUNCTION__,value_len);
				if( m_pircur - m_pirb < value_len )
				{
					len = ::recv(tcon->sock, &m_rdata[m_pircur], m_rdata.size()-m_pircur, MSG_NOSIGNAL);
					if( len < value_len-(m_pircur-m_pirb) )
					{
						tcon->status = TTconPool::NOCON;
		  			LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_pircur);
						return -1;
					}
					m_pircur += len;
				}
				value = &m_rdata[m_pirb];
				if( 0 == i )
				{ 
					count = atoi(value);
					tcount += count;
					LOG_DEBUG(" %s %d %s count : %d  tcount :%d",__FILE__,__LINE__,__FUNCTION__,count,tcount );
				}
				m_pirb += value_len;
				LOG_DEBUG(" %s %d %s value : %s ",__FILE__,__LINE__,__FUNCTION__,string(value,value_len).c_str() );
			  
			}
		}
		m_pirb = m_pircur = 0;
		m_rdata[m_pircur++] = 0;
		*((int *)(&m_rdata[m_pircur])) = htonl(1);
		m_pircur += (int)sizeof(int);
		int n = sprintf(&m_rdata[m_pircur+4],"%d",tcount);
		*((int *)(&m_rdata[m_pircur])) = htonl(n);
		m_pircur += ((int)sizeof(int) + n );
		int len = Send(m_socket,&m_rdata[0],m_pircur);
		LOG_HEX_DEBUG(&m_rdata[0], m_pircur);
		if( len < 0 )
		{
			LOG_ERROR(" %s %d %s send error len %d .",__FILE__,__LINE__,__FUNCTION__,len);
			return -1;
		}
		
		
	return 0;
}

int Session:: do_misc()
{
	int len = 0;
	if( m_picur - m_pib < (int)(sizeof(int)) )
	{
		len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
		if( len < (int)sizeof(int)-m_picur )
		{
	  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
			return -1;
		}
		m_picur += len;
	}
	int fname_len = ntohl( *((int*)(&m_data[m_pib]) ) );
	m_pib += sizeof(int);
	LOG_DEBUG(" %s %d %s fname_len : %d ",__FILE__,__LINE__,__FUNCTION__,fname_len);

	if( m_picur - m_pib < sizeof(int) )
	{
		len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
		if( len < (int)sizeof(int)-(m_picur-m_pib) )
		{
	  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
			return -1;
		}
		m_picur += len;
	}
	int opt = ntohl( *((int*)(&m_data[m_pib])) );
	m_pib += sizeof(int);
	LOG_DEBUG(" %s %d %s opt : %d ",__FILE__,__LINE__,__FUNCTION__,ntohl(opt));

	if( m_picur - m_pib < sizeof(int) )
	{
		len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
		if( len < (int)sizeof(int)-(m_picur-m_pib) )
		{
	  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
			return -1;
		}
		m_picur += len;
	}
	int rnum = ntohl( *((int*)(&m_data[m_pib]) ) );
	int prnum = m_pib;
	m_pib += sizeof(int);
  LOG_DEBUG(" %s %d %s rnum : %d ",__FILE__,__LINE__,__FUNCTION__,rnum);

	if( m_picur - m_pib < fname_len )
	{
		len = ::recv(m_socket, &m_data[m_picur], m_data.size()-m_picur, MSG_NOSIGNAL);
		if( len < fname_len-(m_picur-m_pib) )
		{
	  	LOG_ERROR(" %s %d %s recv date len not enough len %d .",__FILE__,__LINE__,__FUNCTION__,m_picur);
			return -1;
		}
		m_picur += len;
	}
	char * fname = &m_data[m_pib];
	m_pib += fname_len;
  LOG_DEBUG(" %s %d %s fname : %s ",__FILE__,__LINE__,__FUNCTION__,string(fname,fname_len).c_str());

  if( 0 == strncmp(fname,"put",3) || 0 == strncmp(fname,"putkeep",7) )
  {
  		return do_misc_put(rnum);
  }
	else if( 0 == strncmp(fname,"get",3) )
	{
			return do_misc_get(rnum);
	}
	else if( 0 == strncmp(fname,"setindex",8) )
	{
			return do_misc_setindex(rnum);
	}
	else if( 0 == strncmp(fname,"search",6) )
	{
		return do_misc_search(rnum,prnum);
	}
	else if( 0 == strncmp(fname,"out",3) )
	{
		return do_misc_out(rnum);
	}
	else
	{
		LOG_DEBUG(" %s %d %s not support functino . ",__FILE__,__LINE__,__FUNCTION__);
	}
	LOG_DEBUG(" %s %d %s misc end . ",__FILE__,__LINE__,__FUNCTION__);
  return 0;
}

