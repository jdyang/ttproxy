#include "udpsession.h"
#include "log.h"
#include "global.h"
#include "tinyxml.h"
#include <sstream>
#include <unistd.h>
#include <ctype.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <errno.h>
#include <stdlib.h>
#include <signal.h>
#include <sys/epoll.h>
#include <fcntl.h>
#include <pthread.h>
#include <errno.h>
#include <algorithm>
#include <string.h>


static string CMD_RELOAD = "RELOAD CIRCLE";
static string CMD_RELOAD_OK = "RELOAD CIRCLE OK";
static string CMD_HASH_DONE = "HASH DONE";
static string CMD_HASH_DONE_OK = "HASH DONE OK";


bool blankadjacent(int i, int j)
{
	
	if( i==j && ' ' == i ) return true;
	return false;  
	
}
    
void trim(string& line)
{
    string sblank=" ";
    string snul;
    basic_string<char>::iterator ite = line.begin();
    basic_string<char>::iterator iter = line.begin();
  	
	while( ite != line.end()  ){
    if( (ite = find(line.begin(),line.end(),'\t')) != line.end() ){
      iter = ite + 1 ;
      line.replace(ite,iter,sblank);       
      ite = line.begin();
    }else if( (ite = find(line.begin(),line.end(),'\r')) != line.end() ){ //去掉'\r'(回车),兼容windows txt格式
      iter = ite + 1 ;
      line.replace(ite,iter,snul);       
      ite = line.begin();
    }
    else if( (ite = find(line.begin(),line.end(),'\n')) != line.end() ){ //去掉'\r'(回车),兼容windows txt格式
      iter = ite + 1 ;
      line.replace(ite,iter,snul);       
      ite = line.begin();
    }
	}
}
    
UdpSession:: UdpSession(string ip,int port)
{
	struct sockaddr_in sin;
  int address_size;
	bzero(&sin, sizeof(sin));
  sin.sin_family = AF_INET;
  sin.sin_addr.s_addr = inet_addr(ip.c_str());
  sin.sin_port = htons(port);

  m_socket = socket(AF_INET, SOCK_DGRAM, 0);
  if (-1 == m_socket)
  {
  	LOG_ERROR(" %s %d %s socket error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
    exit(1);
  }
  int n = bind(m_socket, (struct sockaddr *)&sin, sizeof(sin));
  if (-1 == n)
  {
  	LOG_ERROR(" %s %d %s bind error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
    exit(1);
  }
}

UdpSession:: ~UdpSession()
{
	close(m_socket);
}

void UdpSession:: process()
{
	struct sockaddr_in sin;
	size_t addr_len = sizeof(struct sockaddr_in);
	while(true)
	{
		int n = recvfrom(m_socket, m_data, 128, 0, (struct sockaddr *)&sin, (socklen_t*)(&addr_len));
		if( -1 == n )
		{
			LOG_ERROR(" %s %d %s recvfrom error %d %s.",__FILE__,__LINE__,__FUNCTION__,errno ,strerror(errno));
			sleep(1);
			continue;
		}
		string cmd = string(m_data,n);
    trim(cmd);
    cmd.resize(unique(cmd.begin(),cmd.end(), blankadjacent) - cmd.begin());
    if(cmd.length()<=0 ) continue;
    if( ' ' == cmd.at(0)) cmd = cmd.substr(1,cmd.length()-1);			
        
		LOG_INFO(" %s %d %s recv udp cmd  %s.",__FILE__,__LINE__,__FUNCTION__,cmd.c_str());
		if( CMD_RELOAD == cmd )
		{
			reloadtt();
			sendto(m_socket,CMD_RELOAD_OK.c_str(),CMD_RELOAD_OK.length(),MSG_NOSIGNAL,(const sockaddr*)&sin,sizeof(struct sockaddr_in));
		}
		else
		if( CMD_HASH_DONE == cmd )
		{
			for(map<string ,TTCircle*>::iterator it = g_ttc.begin();it!= g_ttc.end();it++)
			{
				it->second->finish_consist();
			}
			sendto(m_socket,CMD_HASH_DONE_OK.c_str(),CMD_HASH_DONE_OK.length(),MSG_NOSIGNAL,(const sockaddr*)&sin,sizeof(struct sockaddr_in));
		}
		else
		{
			LOG_ERROR(" %s %d %s not support cmd  [%s].",__FILE__,__LINE__,__FUNCTION__,cmd.c_str());
		}
	}
}

void UdpSession:: reloadtt()
{
	TiXmlDocument *myDocument = new TiXmlDocument(g_cfg.c_str());
	myDocument->LoadFile();
  TiXmlElement *RootElement = myDocument->RootElement();
  TiXmlElement *ttcElement  =  RootElement->FirstChildElement("ttc");
	TiXmlElement *childElement = ttcElement->FirstChildElement("table");
  string table = childElement->GetText();
  list<string> lnode;
	TiXmlElement *ttElement  =  ttcElement->FirstChildElement("tt");
	childElement = ttElement->FirstChildElement("ip");
	string ip = childElement->GetText();
	childElement = ttElement->FirstChildElement("port");
	string port = childElement->GetText();
	childElement = ttElement->FirstChildElement("name");
	string name = childElement->GetText();
	map<string ,TTCircle*>::iterator iter = g_ttc.find(table);
	if( g_ttc.end() != iter )
	{
		if( !iter->second->exist(name))
		{
				iter->second->add_server(name,ip,atoi(port.c_str()));
				LOG_ERROR(" %s %d %s add server name %s ip %s port %s .",__FILE__,__LINE__,__FUNCTION__,name.c_str(),ip.c_str(),port.c_str());
		}
		lnode.push_back(name);
	}
	
	while(true)
  {
  	ttElement  =  ttElement->NextSiblingElement("tt");
    if(NULL == ttElement ) break;
    childElement = ttElement->FirstChildElement("ip");
		ip = childElement->GetText();
		childElement = ttElement->FirstChildElement("port");
		port = childElement->GetText();
		childElement = ttElement->FirstChildElement("name");
		name = childElement->GetText();
		if( g_ttc.end() != iter )
		{
			if( !iter->second->exist(name))
			{
					iter->second->add_server(name,ip,atoi(port.c_str()));
					LOG_ERROR(" %s %d %s add server name %s ip %s port %s .",__FILE__,__LINE__,__FUNCTION__,name.c_str(),ip.c_str(),port.c_str());
			}
			lnode.push_back(name);
		}
	}
	if( g_ttc.end() != iter )
	{
		list<string> ldelnode;
		for(map<string,pair<string,int> >::iterator ite = iter->second->m_server.begin();ite!= iter->second->m_server.end();ite++)
		{
			if( lnode.end() == find(lnode.begin(),lnode.end(),ite->first))
			{
				ldelnode.push_back(ite->first);
			}
		}
		for(list<string>::iterator it=ldelnode.begin();it!=ldelnode.end();it++)
		{
			LOG_ERROR(" %s %d %s  del server name: %s .",__FILE__,__LINE__,__FUNCTION__,it->c_str());
			iter->second->del_server(*it);
		}
	}
	while(true)
	{
		lnode.clear();
		ttcElement  =  ttcElement->NextSiblingElement("ttc");
		if(NULL == ttcElement ) break;
		childElement = ttcElement->FirstChildElement("table");
	  table = childElement->GetText();
		ttElement  =  ttcElement->FirstChildElement("tt");
		childElement = ttElement->FirstChildElement("ip");
		ip = childElement->GetText();
		childElement = ttElement->FirstChildElement("port");
		port = childElement->GetText();
		childElement = ttElement->FirstChildElement("name");
		name = childElement->GetText();
		iter = g_ttc.find(table);
		if( g_ttc.end() != iter )
		{
			if( !iter->second->exist(name))
			{
				iter->second->add_server(name,ip,atoi(port.c_str()));
				LOG_ERROR(" %s %d %s add server name %s ip %s port %s .",__FILE__,__LINE__,__FUNCTION__,name.c_str(),ip.c_str(),port.c_str());
			}
			lnode.push_back(name);
		}

		while(true)
	  {
	  	ttElement  =  ttElement->NextSiblingElement("tt");
	    if(NULL == ttElement ) break;
	    childElement = ttElement->FirstChildElement("ip");
			ip = childElement->GetText();
			childElement = ttElement->FirstChildElement("port");
			port = childElement->GetText();
			childElement = ttElement->FirstChildElement("name");
			name = childElement->GetText();
			if( g_ttc.end() != iter )
			{
				if( !iter->second->exist(name))
				{
					iter->second->add_server(name,ip,atoi(port.c_str()));
					LOG_ERROR(" %s %d %s add server name %s ip %s port %s .",__FILE__,__LINE__,__FUNCTION__,name.c_str(),ip.c_str(),port.c_str());
				}
				lnode.push_back(name);
			}
		}
		if( g_ttc.end() != iter )
		{
			list<string> ldelnode;
			for(map<string,pair<string,int> >::iterator ite = iter->second->m_server.begin();ite!= iter->second->m_server.end();ite++)
			{
				if( lnode.end() == find(lnode.begin(),lnode.end(),ite->first))
				{
					ldelnode.push_back(ite->first);
				}
			}
			for(list<string>::iterator it=ldelnode.begin();it!=ldelnode.end();it++)
			{
				LOG_ERROR(" %s %d %s  del server name: %s .",__FILE__,__LINE__,__FUNCTION__,it->c_str());
				iter->second->del_server(*it);
			}
		}
	}
	delete myDocument;
/*
	Config cfg(g_cfg.c_str());
	
	
	int tnum = cfg.getMatchNum("TTC");
	for( int t =0 ;t < tnum; t++)
	{
		stringstream ttckey;
		ttckey<<"TTC"<<t;
		string table = cfg.get(ttckey.str(),"TABLE");
		map<string ,TTCircle*>::iterator iter = g_ttc.find(table);
		if( g_ttc.end() == iter )
		{
			continue;
		}
		
		int num = cfg.getNum(ttckey.str());
		if( iter->second->m_pool.size() == num && !iter->second->Consist() )
		{
			continue;
		}

		num = num / 3;
		for(int i=0; i<num; i++)
		{
			stringstream sip;
			sip<<"IP"<<i;
			stringstream sport;
			sport<<"PORT"<<i;
			stringstream sname;
			sname<<"NAME"<<i;
			string ip = cfg.get(ttckey.str(),sip.str());
			int port = atoi(cfg.get(ttckey.str(),sport.str()).c_str());
			string name = cfg.get(ttckey.str(),sname.str());
			if( iter->second->exist(name))
			{
				continue;
			}
			iter->second->add_server(name,ip,port);
			LOG_ERROR(" %s %d %s add server name %s ip %s port %d .",__FILE__,__LINE__,__FUNCTION__,name.c_str(),ip.c_str(),port);
		}

		list<string> lname;
		for(map<string,pair<string,int> >::iterator ite = iter->second->m_server.begin();ite!= iter->second->m_server.end();ite++)
		{
			int k =0;
			for(k=0; k<num; k++)
			{
				stringstream sname;
				sname<<"NAME"<<k;
				string name = cfg.get(ttckey.str(),sname.str());
				if( name == ite->first )
				{
					break;
				}
			}
			if( k == num )
			{
				lname.push_back(ite->first);
			}
		}
		for(list<string>::iterator it = lname.begin();it!=lname.end();it++)
		{
			LOG_ERROR(" %s %d %s  del server name: %s .",__FILE__,__LINE__,__FUNCTION__,it->c_str());
			iter->second->del_server(*it);
		}		
		
	}
*/
}
